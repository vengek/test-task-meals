<?php declare(strict_types=1);

namespace Meals\Application\Feature\Poll\UseCase\EmployeeOrdersADish;

use Meals\Application\Component\Provider\EmployeeProviderInterface;
use Meals\Application\Component\Provider\PollResultProvider;
use Meals\Application\Component\Validator\DateTimeInRangeValidator;
use Meals\Application\Component\Validator\UserHasAccessToParticipateInPollsValidator;
use Meals\Application\Feature\Poll\UseCase\EmployeeGetsActivePoll\Interactor as ActivePollInteractor;
use Meals\Domain\Poll\PollResult;

final class Interactor
{
    private const CORRECT_DAY_TO_ORDER_A_DISH = 'Monday this week';
    private const MIN_TIME_TO_ORDER_A_DISH = '06:00';
    private const MAX_TIME_TO_ORDER_A_DISH = '22:00';

    private $getActivePollUseCase;
    private $permissionValidator;
    private $dateTimeInRangeValidator;
    private $pollResultProvider;
    private $employeeProvider;

    public function __construct(
        ActivePollInteractor $getActivePollUseCase,
        UserHasAccessToParticipateInPollsValidator $permissionValidator,
        DateTimeInRangeValidator $dateTimeInRangeValidator,
        PollResultProvider $pollResultProvider,
        EmployeeProviderInterface $employeeProvider
    ) {
        $this->getActivePollUseCase = $getActivePollUseCase;
        $this->permissionValidator = $permissionValidator;
        $this->dateTimeInRangeValidator = $dateTimeInRangeValidator;
        $this->pollResultProvider = $pollResultProvider;
        $this->employeeProvider = $employeeProvider;
    }

    public function orderADish(int $employeeId, int $pollId, int $dishId, \DateTime $orderTime): void
    {
        $employee = $this->employeeProvider->getEmployee($employeeId);
        $this->permissionValidator->validate($employee->getUser());

        $activePoll = $this->getActivePollUseCase->getActivePoll($employeeId, $pollId);
        $this->ensureTheTimeForTheOrderIsCorrect($orderTime);

        $dish = $activePoll->getMenu()->chooseADish($dishId);
        $pollResult = new PollResult($this->pollResultProvider->generateId(), $activePoll, $employee, $dish);
        $this->pollResultProvider->save($pollResult);
    }

    private function ensureTheTimeForTheOrderIsCorrect(\DateTime $orderTime): void
    {
        $minTimeToOrderADishString = self::CORRECT_DAY_TO_ORDER_A_DISH . ' ' . self::MIN_TIME_TO_ORDER_A_DISH;
        $maxTimeToOrderADishString = self::CORRECT_DAY_TO_ORDER_A_DISH . ' ' . self::MAX_TIME_TO_ORDER_A_DISH;
        $this->dateTimeInRangeValidator->validate(
            $orderTime,
            new \DateTime($minTimeToOrderADishString),
            new \DateTime($maxTimeToOrderADishString)
        );
    }
}
