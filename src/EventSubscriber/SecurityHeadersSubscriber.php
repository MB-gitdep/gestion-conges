<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Ajoute les en-têtes de sécurité HTTP recommandés sur chaque réponse :
 * limite le clickjacking, le MIME-sniffing, force HTTPS, et restreint
 * les sources de contenu autorisées (CSP).
 */
class SecurityHeadersSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly string $environment)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), camera=(), microphone=()');

        // CSP : autorise Bootstrap via CDN (utilisé dans les templates) + ressources locales
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; "
            . "style-src 'self' https://cdn.jsdelivr.net 'unsafe-inline'; "
            . "script-src 'self' https://cdn.jsdelivr.net; "
            . "img-src 'self' data:; "
            . "frame-ancestors 'none'; "
            . "form-action 'self'; "
            . "base-uri 'self'"
        );

        // HSTS uniquement en production et en HTTPS (jamais en dev, casserait le http local)
        if ('prod' === $this->environment && $event->getRequest()->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
    }
}
