<?php

namespace App\Exception;

/**
 * Levée quand l'utilisateur ne dispose pas d'assez de jours restants
 * sur le type de congé demandé.
 */
class SoldeInsuffisantException extends \DomainException
{
}
