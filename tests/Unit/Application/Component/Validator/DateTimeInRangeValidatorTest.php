<?php declare(strict_types=1);

namespace tests\Meals\Unit\Application\Component\Validator;

use Meals\Application\Component\Validator\DateTimeInRangeValidator;
use Meals\Application\Component\Validator\Exception\DateTimeIsOutOfRangeException;
use PHPUnit\Framework\TestCase;

class DateTimeInRangeValidatorTest extends TestCase
{
    public function testSuccessful()
    {
        $current = new \DateTime();
        $min = new \DateTime('last year');
        $max = new \DateTime('next year');

        $validator = new DateTimeInRangeValidator();

        verify($validator->validate($current, $min, $max))->null();
    }

    public function testFail()
    {
        $this->expectException(DateTimeIsOutOfRangeException::class);
        $current = new \DateTime();
        $min = new \DateTime('next year');
        $max = new \DateTime('last year');

        $validator = new DateTimeInRangeValidator();

        verify($validator->validate($current, $min, $max));
    }
}
