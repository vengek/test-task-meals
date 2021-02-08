<?php declare(strict_types=1);

namespace Meals\Application\Component\Provider;

use Meals\Domain\Poll\PollResult;

interface PollResultProvider
{
    public function save(PollResult $pollResult): void;

    public function generateId(): int;
}
