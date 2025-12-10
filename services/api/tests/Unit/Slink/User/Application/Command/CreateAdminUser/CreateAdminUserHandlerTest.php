<?php

declare(strict_types=1);

namespace Unit\Slink\User\Application\Command\CreateAdminUser;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Application\Command\CreateAdminUser\CreateAdminUserCommand;
use Slink\User\Application\Command\CreateAdminUser\CreateAdminUserHandler;
use Slink\User\Domain\Context\SystemChangeUserRoleContext;
use Slink\User\Domain\Context\UserCreationContext;
use Slink\User\Domain\Factory\AdminUserFactory;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\Specification\UniqueDisplayNameSpecificationInterface;
use Slink\User\Domain\Specification\UniqueEmailSpecificationInterface;
use Slink\User\Domain\Specification\UniqueUsernameSpecificationInterface;
use Slink\User\Domain\Specification\UserRoleExistSpecificationInterface;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Username;

final class CreateAdminUserHandlerTest extends TestCase {
  #[Test]
  public function itCreatesAdminUserWhenNotExists(): void {
    $userRepository = $this->createMock(UserStoreRepositoryInterface::class);
    $userRepository->method('getByUsername')->willReturn(null);
    $userRepository->expects($this->once())
      ->method('store')
      ->with($this->isInstanceOf(User::class));

    $handler = $this->createHandler($userRepository, 'admin', 'admin@test.com', 'password123');

    $handler(new CreateAdminUserCommand());
  }

  #[Test]
  public function itSkipsCreationWhenUserExistsByUsername(): void {
    $existingUser = $this->createMock(User::class);

    $userRepository = $this->createMock(UserStoreRepositoryInterface::class);
    $userRepository->method('getByUsername')
      ->willReturnCallback(function ($identifier) use ($existingUser) {
        if ($identifier instanceof Username) {
          return $existingUser;
        }
        return null;
      });
    $userRepository->expects($this->never())->method('store');

    $handler = $this->createHandler($userRepository, 'admin', 'admin@test.com', 'password123');

    $handler(new CreateAdminUserCommand());
  }

  #[Test]
  public function itSkipsCreationWhenUserExistsByEmail(): void {
    $existingUser = $this->createMock(User::class);

    $userRepository = $this->createMock(UserStoreRepositoryInterface::class);
    $userRepository->method('getByUsername')
      ->willReturnCallback(function ($identifier) use ($existingUser) {
        if ($identifier instanceof Email) {
          return $existingUser;
        }
        return null;
      });
    $userRepository->expects($this->never())->method('store');

    $handler = $this->createHandler($userRepository, 'admin', 'admin@test.com', 'password123');

    $handler(new CreateAdminUserCommand());
  }

  #[Test]
  public function itGrantsAdminRoleToCreatedUser(): void {
    $userRepository = $this->createMock(UserStoreRepositoryInterface::class);
    $userRepository->method('getByUsername')->willReturn(null);
    $userRepository->expects($this->once())
      ->method('store')
      ->with($this->callback(function (User $user) {
        return in_array('ROLE_ADMIN', $user->getRoles(), true);
      }));

    $handler = $this->createHandler($userRepository, 'admin', 'admin@test.com', 'password123');

    $handler(new CreateAdminUserCommand());
  }

  private function createHandler(
    UserStoreRepositoryInterface $userRepository,
    string $username,
    string $email,
    string $password
  ): CreateAdminUserHandler {
    $uniqueEmailSpec = $this->createMock(UniqueEmailSpecificationInterface::class);
    $uniqueEmailSpec->method('isUnique')->willReturn(true);

    $uniqueUsernameSpec = $this->createMock(UniqueUsernameSpecificationInterface::class);
    $uniqueUsernameSpec->method('isUnique')->willReturn(true);

    $uniqueDisplayNameSpec = $this->createMock(UniqueDisplayNameSpecificationInterface::class);
    $uniqueDisplayNameSpec->method('isUnique')->willReturn(true);

    $userCreationContext = new UserCreationContext($uniqueEmailSpec, $uniqueUsernameSpec, $uniqueDisplayNameSpec);
    $adminUserFactory = new AdminUserFactory($userCreationContext, $username, $email, $password);

    $roleExistSpec = $this->createMock(UserRoleExistSpecificationInterface::class);
    $roleExistSpec->method('isSatisfiedBy')->willReturn(true);

    $systemChangeUserRoleContext = new SystemChangeUserRoleContext($roleExistSpec);

    return new CreateAdminUserHandler($userRepository, $adminUserFactory, $systemChangeUserRoleContext);
  }
}
