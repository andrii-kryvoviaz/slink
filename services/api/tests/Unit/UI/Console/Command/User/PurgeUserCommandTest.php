<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Console\Command\User;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Application\Command\PurgeUser\PurgeUserCommand as PurgeUser;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Event\UserWasPurged;
use Slink\User\Domain\Repository\UserRepositoryInterface;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\User;
use Slink\User\Infrastructure\ReadModel\View\UserView;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use UI\Console\Command\User\PurgeUserCommand;

final class PurgeUserCommandTest extends TestCase {
  private const string USER_ID = '11111111-2222-3333-4444-555555555555';
  private const string OTHER_USER_ID = '66666666-7777-8888-9999-aaaaaaaaaaaa';

  private function userView(string $uuid, UserStatus $status, ?string $email = 'member@local.test'): UserView {
    $user = $this->createStub(UserView::class);
    $user->method('getUuid')->willReturn($uuid);
    $user->method('getEmail')->willReturn($email);
    $user->method('getStatus')->willReturn($status->value);

    return $user;
  }

  /**
   * @return \Generator<int, object, void, int>
   */
  private function events(string $uuid, bool $purged): \Generator {
    if ($purged) {
      yield new UserWasPurged(ID::fromString($uuid));
    }

    return 1;
  }

  /**
   * @param array<string, bool> $purgedByUuid
   */
  private function store(array $purgedByUuid): UserStoreRepositoryInterface {
    $store = $this->createStub(UserStoreRepositoryInterface::class);
    $store->method('get')->willReturnCallback(
      fn (ID $id): User => User::reconstituteFromEvents(
        $id,
        $this->events($id->toString(), $purgedByUuid[$id->toString()] ?? false),
      ),
    );

    return $store;
  }

  /**
   * @param array<string, bool> $purgedByUuid
   */
  private function tester(UserRepositoryInterface $repository, CommandBusInterface $bus, array $purgedByUuid = []): CommandTester {
    $command = new PurgeUserCommand($repository, $this->store($purgedByUuid));
    $command->setCommandBus($bus);
    (new Application())->addCommand($command);

    return new CommandTester($command);
  }

  #[Test]
  public function itRefusesNonDeletedUserWithoutForce(): void {
    $repository = $this->createStub(UserRepositoryInterface::class);
    $repository->method('one')->willReturn($this->userView(self::USER_ID, UserStatus::Active));

    $bus = $this->createMock(CommandBusInterface::class);
    $bus->expects(self::never())->method('handle');

    $tester = $this->tester($repository, $bus);
    $exitCode = $tester->execute(['user' => self::USER_ID], ['interactive' => false]);

    self::assertSame(Command::FAILURE, $exitCode);
    self::assertStringContainsString('not soft-deleted', $tester->getDisplay());
  }

  #[Test]
  public function itPurgesNonDeletedUserWithForce(): void {
    $repository = $this->createStub(UserRepositoryInterface::class);
    $repository->method('one')->willReturn($this->userView(self::USER_ID, UserStatus::Active));

    $bus = $this->createMock(CommandBusInterface::class);
    $bus->expects(self::once())
      ->method('handle')
      ->with(self::callback(
        static fn (CommandInterface $command) => $command instanceof PurgeUser && $command->getId() === self::USER_ID,
      ));

    $tester = $this->tester($repository, $bus);
    $exitCode = $tester->execute(['user' => self::USER_ID, '--force' => true], ['interactive' => false]);

    self::assertSame(Command::SUCCESS, $exitCode);
  }

  #[Test]
  public function itRefusesNonInteractivePurgeWithoutForce(): void {
    $repository = $this->createStub(UserRepositoryInterface::class);
    $repository->method('one')->willReturn($this->userView(self::USER_ID, UserStatus::Deleted));

    $bus = $this->createMock(CommandBusInterface::class);
    $bus->expects(self::never())->method('handle');

    $tester = $this->tester($repository, $bus);
    $exitCode = $tester->execute(['user' => self::USER_ID], ['interactive' => false]);

    self::assertSame(Command::FAILURE, $exitCode);
    self::assertStringContainsString('Non-interactive purge requires --force', $tester->getDisplay());
  }

  #[Test]
  public function itFallsBackToUuidInSuccessMessageWhenEmailIsNull(): void {
    $repository = $this->createStub(UserRepositoryInterface::class);
    $repository->method('one')->willReturn($this->userView(self::USER_ID, UserStatus::Active, null));

    $bus = $this->createMock(CommandBusInterface::class);
    $bus->expects(self::once())->method('handle');

    $tester = $this->tester($repository, $bus);
    $exitCode = $tester->execute(['user' => self::USER_ID, '--force' => true], ['interactive' => false]);

    self::assertSame(Command::SUCCESS, $exitCode);
    self::assertStringContainsString(self::USER_ID, $tester->getDisplay());
  }

  #[Test]
  public function itPurgesEveryDeletedUserWithAllDeleted(): void {
    $repository = $this->createStub(UserRepositoryInterface::class);
    $repository->method('findByStatus')->willReturn([
      $this->userView(self::USER_ID, UserStatus::Deleted),
      $this->userView(self::OTHER_USER_ID, UserStatus::Deleted),
    ]);

    $purgedIds = [];
    $bus = $this->createMock(CommandBusInterface::class);
    $bus->expects(self::exactly(2))
      ->method('handle')
      ->willReturnCallback(static function (CommandInterface $command) use (&$purgedIds): void {
        self::assertInstanceOf(PurgeUser::class, $command);
        $purgedIds[] = $command->getId();
      });

    $tester = $this->tester($repository, $bus);
    $exitCode = $tester->execute(['--all-deleted' => true], ['interactive' => false]);

    self::assertSame(Command::SUCCESS, $exitCode);
    self::assertSame([self::USER_ID, self::OTHER_USER_ID], $purgedIds);
  }

  #[Test]
  public function itSkipsAlreadyPurgedUsersWithAllDeleted(): void {
    $repository = $this->createStub(UserRepositoryInterface::class);
    $repository->method('findByStatus')->willReturn([
      $this->userView(self::USER_ID, UserStatus::Deleted),
      $this->userView(self::OTHER_USER_ID, UserStatus::Deleted),
    ]);

    $purgedIds = [];
    $bus = $this->createMock(CommandBusInterface::class);
    $bus->expects(self::once())
      ->method('handle')
      ->willReturnCallback(static function (CommandInterface $command) use (&$purgedIds): void {
        self::assertInstanceOf(PurgeUser::class, $command);
        $purgedIds[] = $command->getId();
      });

    $tester = $this->tester($repository, $bus, [self::USER_ID => true]);
    $tester->setInputs(['y']);
    $exitCode = $tester->execute(['--all-deleted' => true]);

    self::assertSame(Command::SUCCESS, $exitCode);
    self::assertSame([self::OTHER_USER_ID], $purgedIds);
    self::assertStringContainsString('Purge 1 soft-deleted user(s)?', $tester->getDisplay());
  }

  #[Test]
  public function itReportsNothingToPurgeWhenEveryDeletedUserIsAlreadyPurged(): void {
    $repository = $this->createStub(UserRepositoryInterface::class);
    $repository->method('findByStatus')->willReturn([
      $this->userView(self::USER_ID, UserStatus::Deleted),
    ]);

    $bus = $this->createMock(CommandBusInterface::class);
    $bus->expects(self::never())->method('handle');

    $tester = $this->tester($repository, $bus, [self::USER_ID => true]);
    $exitCode = $tester->execute(['--all-deleted' => true], ['interactive' => false]);

    self::assertSame(Command::SUCCESS, $exitCode);
    self::assertStringContainsString('No soft-deleted users left to purge.', $tester->getDisplay());
  }

  #[Test]
  public function itSkipsAlreadyPurgedUserOnSingleUserPath(): void {
    $repository = $this->createStub(UserRepositoryInterface::class);
    $repository->method('one')->willReturn($this->userView(self::USER_ID, UserStatus::Deleted));

    $bus = $this->createMock(CommandBusInterface::class);
    $bus->expects(self::never())->method('handle');

    $tester = $this->tester($repository, $bus, [self::USER_ID => true]);
    $exitCode = $tester->execute(['user' => self::USER_ID, '--force' => true], ['interactive' => false]);

    self::assertSame(Command::SUCCESS, $exitCode);
    self::assertStringContainsString('is already purged', $tester->getDisplay());
  }

  #[Test]
  public function itFallsBackToUuidInAllDeletedLoopWhenEmailIsNull(): void {
    $repository = $this->createStub(UserRepositoryInterface::class);
    $repository->method('findByStatus')->willReturn([
      $this->userView(self::USER_ID, UserStatus::Deleted, null),
    ]);

    $bus = $this->createMock(CommandBusInterface::class);
    $bus->expects(self::once())->method('handle');

    $tester = $this->tester($repository, $bus);
    $exitCode = $tester->execute(['--all-deleted' => true], ['interactive' => false]);

    self::assertSame(Command::SUCCESS, $exitCode);
    self::assertStringContainsString(self::USER_ID, $tester->getDisplay());
  }
}
