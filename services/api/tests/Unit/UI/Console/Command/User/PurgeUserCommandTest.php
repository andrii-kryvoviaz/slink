<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Console\Command\User;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Application\Command\CommandInterface;
use Slink\User\Application\Command\PurgeUser\PurgeUserCommand as PurgeUser;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Repository\UserRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\UserView;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use UI\Console\Command\User\PurgeUserCommand;

final class PurgeUserCommandTest extends TestCase {
  private const string USER_ID = '11111111-2222-3333-4444-555555555555';
  private const string OTHER_USER_ID = '66666666-7777-8888-9999-aaaaaaaaaaaa';

  private function userView(string $uuid, UserStatus $status): UserView {
    $user = $this->createStub(UserView::class);
    $user->method('getUuid')->willReturn($uuid);
    $user->method('getEmail')->willReturn('member@local.test');
    $user->method('getStatus')->willReturn($status->value);

    return $user;
  }

  private function tester(UserRepositoryInterface $repository, CommandBusInterface $bus): CommandTester {
    $command = new PurgeUserCommand($repository);
    $command->setCommandBus($bus);

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
}
