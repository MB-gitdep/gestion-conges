<?php

namespace App\EventSubscriber;

use App\Entity\Utilisateur;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Tant qu'un utilisateur n'a pas remplacé son mot de passe temporaire
 * (attribué par un admin à la création du compte) par un mot de passe
 * de son choix, il est systématiquement redirigé vers la page de
 * changement — impossible d'utiliser l'application avec un mot de
 * passe temporaire au-delà de la première connexion.
 */
class ForcerChangementMotDePasseSubscriber implements EventSubscriberInterface
{
    private const ROUTES_AUTORISEES = [
        'profil_changer_mot_de_passe',
        'app_logout',
    ];

    public function __construct(
        private readonly Security $security,
        private readonly RouterInterface $router,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $utilisateur = $this->security->getUser();

        if (!$utilisateur instanceof Utilisateur || !$utilisateur->doitChangerMotDePasse()) {
            return;
        }

        $route = $event->getRequest()->attributes->get('_route');

        if (in_array($route, self::ROUTES_AUTORISEES, true)) {
            return;
        }

        $event->setResponse(new RedirectResponse($this->router->generate('profil_changer_mot_de_passe')));
    }
}
