<?php declare(strict_types=1);

namespace tests\Meals\Functional\Fake\Provider;

use Meals\Application\Component\Provider\PollResultProvider;
use Meals\Domain\Poll\PollResult;

final class FakePollResultProvider implements PollResultProvider
{
    public function save(PollResult $pollResult): void
    {
    }

    public function generateId(): int
    {
        return (int)hexdec(uniqid("", true));
    }
}
