<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\User\Application\Command\PurgeUser;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Application\Command\PurgeUser\PurgeUserCommand;
use Slink\User\Application\Command\PurgeUser\PurgeUserHandler;
use Slink\User\Domain\Exception\SelfUserPurgeException;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\Specification\CurrentUserSpecificationInterface;
use Slink\User\Domain\User;

final class PurgeUserHandlerTest extends TestCase {

  #[Test]
  public function itPurgesUserAndStoresAggregate(): void {
    $userId = ID::generate();

    $owner = $this->createMock(User::class);
    $owner->expects($this->once())->method('purge');

    $userStore = $this->createMock(UserStoreRepositoryInterface::class);
    $userStore->expects($this->once())
      ->method('get')
      ->with($this->callback(fn(ID $id) => $id->toString() === $userId->toString()))
      ->willReturn($owner);
    $userStore->expects($this->once())
      ->method('store')
      ->with($owner);

    $sameUserSpecification = $this->createStub(CurrentUserSpecificationInterface::class);
    $sameUserSpecification->method('isSatisfiedBy')->willReturn(false);

    $handler = new PurgeUserHandler($userStore, $sameUserSpecification);

    $handler(new PurgeUserCommand($userId->toString()));
  }

  #[Test]
  public function itThrowsWhenAdminPurgesOwnAccount(): void {
    $adminId = ID::generate();

    $userStore = $this->createMock(UserStoreRepositoryInterface::class);
    $userStore->expects($this->never())->method('get');
    $userStore->expects($this->never())->method('store');

    $sameUserSpecification = $this->createStub(CurrentUserSpecificationInterface::class);
    $sameUserSpecification->method('isSatisfiedBy')->willReturn(true);

    $handler = new PurgeUserHandler($userStore, $sameUserSpecification);

    $this->expectException(SelfUserPurgeException::class);

    $handler(new PurgeUserCommand($adminId->toString()));
  }
}
