<?php

namespace App\Exception;

/**
 * Levée quand la période demandée est incohérente
 * (date de fin avant date de début, période dans le passé, etc.).
 */
class PeriodeInvalideException extends \DomainException
{
}
