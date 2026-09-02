<?php

namespace App\EventSubscriber;

use App\Entity\Conge;
use App\Entity\CongeHistorique;
use App\Entity\UtilisateurService;
use App\Event\CongeDemandeCreeeEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Workflow\Event\CompletedEvent;

/**
 * Centralise tout ce qui doit se passer automatiquement autour d'une
 * demande de congé : email au(x) responsable(s) à la création, email au
 * demandeur + écriture de l'historique à chaque décision.
 */
class CongeNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
        private readonly string $emailExpediteur = 'no-reply@gestion-conges.local',
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CongeDemandeCreeeEvent::NAME => 'onDemandeCreee',
            'workflow.conge.completed' => 'onTransitionCompletee',
        ];
    }

    public function onDemandeCreee(CongeDemandeCreeeEvent $event): void
    {
        $conge = $event->getConge();
        $demandeur = $conge->getUtilisateur();

        $responsables = [];
        foreach ($demandeur->getUtilisateurServices() as $utilisateurService) {
            foreach ($utilisateurService->getService()->getUtilisateurServices() as $autre) {
                if ($autre->isEstResponsable() && $autre->getUtilisateur() !== $demandeur) {
                    $responsables[$autre->getUtilisateur()->getId()] = $autre->getUtilisateur();
                }
            }
        }

        foreach ($responsables as $responsable) {
            $email = (new TemplatedEmail())
                ->from(new Address($this->emailExpediteur, 'Gestion des congés'))
                ->to($responsable->getEmail())
                ->subject(sprintf('Nouvelle demande de congé de %s', $demandeur))
                ->htmlTemplate('emails/conge_demande.html.twig')
                ->context(['conge' => $conge, 'responsable' => $responsable]);

            $this->mailer->send($email);
        }
    }

    public function onTransitionCompletee(CompletedEvent $event): void
    {
        $conge = $event->getSubject();

        if (!$conge instanceof Conge) {
            return;
        }

        $transition = $event->getTransition();
        $ancienStatut = $transition->getFroms()[0] ?? '';
        $nouveauStatut = $transition->getTos()[0] ?? '';

        // Historisation systématique de la transition
        $historique = new CongeHistorique();
        $historique
            ->setConge($conge)
            ->setUtilisateur($this->security->getUser())
            ->setAncienStatut($ancienStatut)
            ->setNouveauStatut($nouveauStatut);

        $this->em->persist($historique);
        $this->em->flush();

        // Email au demandeur pour l'informer de la décision
        $email = (new TemplatedEmail())
            ->from(new Address($this->emailExpediteur, 'Gestion des congés'))
            ->to($conge->getUtilisateur()->getEmail())
            ->subject(sprintf(
                'Votre demande de congé a été %s',
                'valider' === $transition->getName() ? 'validée' : 'refusée'
            ))
            ->htmlTemplate('emails/conge_reponse.html.twig')
            ->context(['conge' => $conge]);

        $this->mailer->send($email);
    }
}
