<?php

namespace App\Exception;

/**
 * Levée quand la période demandée chevauche une autre demande
 * déjà en attente ou déjà validée pour le même utilisateur.
 */
class PeriodeChevaucheeException extends \DomainException
{
}
