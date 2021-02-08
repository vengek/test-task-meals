<?php declare(strict_types=1);

namespace tests\Meals\Functional\Interactor;

use Meals\Application\Component\Validator\Exception\AccessDeniedException;
use Meals\Application\Component\Validator\Exception\DateTimeIsOutOfRangeException;
use Meals\Application\Component\Validator\Exception\PollIsNotActiveException;
use Meals\Application\Feature\Poll\UseCase\EmployeeOrdersADish\Interactor;
use Meals\Domain\Dish\Dish;
use Meals\Domain\Dish\DishList;
use Meals\Domain\Employee\Employee;
use Meals\Domain\Menu\Menu;
use Meals\Domain\Menu\ThereIsNoSuchDishOnTheMenu;
use Meals\Domain\Poll\Poll;
use Meals\Domain\User\Permission\Permission;
use Meals\Domain\User\Permission\PermissionList;
use Meals\Domain\User\User;
use tests\Meals\Functional\Fake\Provider\FakeEmployeeProvider;
use tests\Meals\Functional\Fake\Provider\FakePollProvider;
use tests\Meals\Functional\FunctionalTestCase;

class EmployeeOrdersADishTest extends FunctionalTestCase
{
    public function testSuccessful()
    {
        $dishId = 1;

        verify(
            $this->performTestMethod(
                $this->getEmployeeWithPermissions(),
                $this->getPoll(true, $this->getMenuWithChosenDish($dishId)),
                $dishId,
                new \DateTime('monday this week 07:00')
            )
        )->null();
    }

    public function testUserHasNotPermissions()
    {
        $this->expectException(AccessDeniedException::class);

        $dishId = 1;
        $this->performTestMethod(
            $this->getEmployeeWithNoPermissions(),
            $this->getPoll(true, $this->getMenuWithChosenDish($dishId)),
            $dishId,
            new \DateTime('monday this week 07:00')
        );
    }

    public function testPollIsNotActive()
    {
        $this->expectException(PollIsNotActiveException::class);

        $dishId = 1;
        $this->performTestMethod(
            $this->getEmployeeWithPermissions(),
            $this->getPoll(false, $this->getMenuWithChosenDish($dishId)),
            $dishId,
            new \DateTime('monday this week 07:00')
        );
    }

    public function testIncorrectDayForTheOrder()
    {
        $this->expectException(DateTimeIsOutOfRangeException::class);

        $dishId = 1;
        $this->performTestMethod(
            $this->getEmployeeWithPermissions(),
            $this->getPoll(true, $this->getMenuWithChosenDish($dishId)),
            $dishId,
            new \DateTime('sunday next week 07:00')
        );
    }

    public function testIncorrectTimeForTheOrder()
    {
        $this->expectException(DateTimeIsOutOfRangeException::class);

        $dishId = 1;
        $this->performTestMethod(
            $this->getEmployeeWithPermissions(),
            $this->getPoll(true, $this->getMenuWithChosenDish($dishId)),
            $dishId,
            new \DateTime('monday this week 23:00')
        );
    }

    public function testDishIsNotOnMenu()
    {
        $this->expectException(ThereIsNoSuchDishOnTheMenu::class);

        $dishId = 1;
        $this->performTestMethod(
            $this->getEmployeeWithPermissions(),
            $this->getPoll(true, $this->getMenuWithoutChosenDish()),
            $dishId,
            new \DateTime('monday this week 22:00')
        );
    }

    private function performTestMethod(Employee $employee, Poll $poll, int $dishId, \DateTime $orderTime): void
    {
        $this->getContainer()->get(FakeEmployeeProvider::class)->setEmployee($employee);
        $this->getContainer()->get(FakePollProvider::class)->setPoll($poll);

        $this->getContainer()->get(Interactor::class)->orderADish($employee->getId(), $poll->getId(), $dishId, $orderTime);
    }

    private function getEmployeeWithPermissions(): Employee
    {
        return new Employee(
            1,
            $this->getUserWithPermissions(),
            4,
            'Surname'
        );
    }

    private function getUserWithPermissions(): User
    {
        return new User(
            1,
            new PermissionList(
                [
                    new Permission(Permission::PARTICIPATION_IN_POLLS),
                    new Permission(Permission::VIEW_ACTIVE_POLLS)
                ]
            ),
        );
    }

    private function getEmployeeWithNoPermissions(): Employee
    {
        return new Employee(
            1,
            $this->getUserWithNoPermissions(),
            4,
            'Surname'
        );
    }

    private function getUserWithNoPermissions(): User
    {
        return new User(
            1,
            new PermissionList([]),
        );
    }

    private function getPoll(bool $active, Menu $menu): Poll
    {
        return new Poll(
            1,
            $active,
            $menu
        );
    }

    private function getMenuWithChosenDish(int $dishId): Menu
    {
        return new Menu(
            1,
            'title',
            new DishList([new Dish($dishId, 'some dish', 'delicious dish')]),
        );
    }

    private function getMenuWithoutChosenDish(): Menu
    {
        return new Menu(
            1,
            'title',
            new DishList([]),
        );
    }
}
