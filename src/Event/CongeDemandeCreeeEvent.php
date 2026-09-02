<?php

namespace App\Event;

use App\Entity\Conge;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Déclenché juste après la création d'une nouvelle demande de congé,
 * pour permettre l'envoi automatique d'un email au(x) responsable(s).
 */
class CongeDemandeCreeeEvent extends Event
{
    public const NAME = 'conge.demande_creee';

    public function __construct(private readonly Conge $conge)
    {
    }

    public function getConge(): Conge
    {
        return $this->conge;
    }
}
