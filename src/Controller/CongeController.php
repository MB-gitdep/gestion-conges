<?php

namespace App\Controller;

use App\Entity\Conge;
use App\Entity\Utilisateur;
use App\Event\CongeDemandeCreeeEvent;
use App\Exception\PeriodeChevaucheeException;
use App\Exception\PeriodeInvalideException;
use App\Exception\SoldeInsuffisantException;
use App\Form\CongeType;
use App\Repository\CongeRepository;
use App\Repository\SoldeCongeRepository;
use App\Service\CongeValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/mes-demandes')]
#[IsGranted('ROLE_USER')]
class CongeController extends AbstractController
{
    #[Route('', name: 'conge_index', methods: ['GET'])]
    public function index(CongeRepository $congeRepository, SoldeCongeRepository $soldeCongeRepository): Response
    {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        return $this->render('conge/index.html.twig', [
            'conges' => $congeRepository->findBy(['utilisateur' => $utilisateur], ['dateDebut' => 'DESC']),
            'soldes' => $soldeCongeRepository->findBy(['utilisateur' => $utilisateur]),
        ]);
    }

    #[Route('/nouvelle', name: 'conge_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher,
        CongeValidationService $validationService,
        LoggerInterface $logger
    ): Response {
        $conge = new Conge();
        $conge->setUtilisateur($this->getUser());

        $form = $this->createForm(CongeType::class, $conge);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $validationService->validerNouvelleDemande($conge);

                $em->persist($conge);
                $em->flush();

                $dispatcher->dispatch(new CongeDemandeCreeeEvent($conge), CongeDemandeCreeeEvent::NAME);

                $this->addFlash('success', 'Votre demande de congé a été envoyée.');

                return $this->redirectToRoute('conge_index');
            } catch (PeriodeInvalideException|PeriodeChevaucheeException|SoldeInsuffisantException $e) {
                // Exceptions métier attendues : message clair renvoyé à l'utilisateur,
                // rien à journaliser en erreur (comportement normal de l'application).
                $this->addFlash('error', $e->getMessage());
            } catch (\Throwable $e) {
                // Tout le reste est inattendu : on journalise pour investigation,
                // et on ne renvoie jamais le détail technique à l'utilisateur.
                $logger->error('Échec inattendu lors de la création d\'une demande de congé', [
                    'exception' => $e,
                    'utilisateur' => $this->getUser()?->getUserIdentifier(),
                ]);
                $this->addFlash('error', 'Une erreur est survenue, veuillez réessayer ou contacter le support.');
            }
        }

        return $this->render('conge/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'conge_show', methods: ['GET'])]
    #[IsGranted('CONGE_VOIR', subject: 'conge')]
    public function show(Conge $conge): Response
    {
        return $this->render('conge/show.html.twig', [
            'conge' => $conge,
        ]);
    }
}