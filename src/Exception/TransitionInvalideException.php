<?php

namespace App\Exception;

/**
 * Levée quand on tente d'appliquer une transition de workflow
 * qui n'est plus possible dans l'état courant de la demande
 * (ex. demande déjà traitée entre-temps par quelqu'un d'autre).
 */
class TransitionInvalideException extends \DomainException
{
}
