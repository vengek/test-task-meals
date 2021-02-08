<?php declare(strict_types=1);

namespace Meals\Application\Component\Validator;

use Meals\Application\Component\Validator\Exception\DateTimeIsOutOfRangeException;

class DateTimeInRangeValidator
{
    public function validate(\DateTime $current, \DateTime $min, \DateTime $max): void
    {
        if ($min <= $current && $current <= $max) {
            return;
        }

        throw new DateTimeIsOutOfRangeException();
    }
}
