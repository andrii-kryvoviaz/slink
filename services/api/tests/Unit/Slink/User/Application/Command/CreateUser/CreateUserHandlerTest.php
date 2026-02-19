<?php

declare(strict_types=1);

namespace Unit\Slink\User\Application\Command\CreateUser;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\User\Application\Command\CreateUser\CreateUserCommand;
use Slink\User\Application\Command\CreateUser\CreateUserHandler;
use Slink\User\Domain\Context\UserCreationContext;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Exception\RegistrationIsNotAllowed;
use Slink\User\Domain\Factory\UserFactory;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\Specification\UniqueDisplayNameSpecificationInterface;
use Slink\User\Domain\Specification\UniqueEmailSpecificationInterface;
use Slink\User\Domain\Specification\UniqueUsernameSpecificationInterface;
use Slink\User\Domain\User;

final class CreateUserHandlerTest extends TestCase {

  #[Test]
  public function itCreatesUserWithExplicitActiveStatus(): void {
    $userRepository = $this->createMock(UserStoreRepositoryInterface::class);
    $userRepository->expects($this->once())
      ->method('store')
      ->with($this->callback(function (User $user) {
        return $user->getStatus() === UserStatus::Active;
      }));

    $userFactory = $this->createUserFactory();

    $handler = new CreateUserHandler($userRepository, $userFactory);

    $command = new CreateUserCommand(
      email: 'test@example.com',
      password: 'password123',
      username: 'testuser',
      displayName: 'Test User',
      status: UserStatus::Active,
    );

    $handler($command);
  }

  #[Test]
  public function itCreatesUserWithExplicitInactiveStatus(): void {
    $userRepository = $this->createMock(UserStoreRepositoryInterface::class);
    $userRepository->expects($this->once())
      ->method('store')
      ->with($this->callback(function (User $user) {
        return $user->getStatus() === UserStatus::Inactive;
      }));

    $userFactory = $this->createUserFactory();

    $handler = new CreateUserHandler($userRepository, $userFactory);

    $command = new CreateUserCommand(
      email: 'test@example.com',
      password: 'password123',
      username: 'testuser',
      displayName: 'Test User',
      status: UserStatus::Inactive,
    );

    $handler($command);
  }

  #[Test]
  public function itDerivesActiveStatusWhenApprovalNotRequired(): void {
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->willReturnMap([
        ['user.allowRegistration', true],
        ['user.approvalRequired', false]
      ]);

    $userRepository = $this->createMock(UserStoreRepositoryInterface::class);
    $userRepository->expects($this->once())
      ->method('store')
      ->with($this->callback(function (User $user) {
        return $user->getStatus() === UserStatus::Active;
      }));

    $userFactory = $this->createUserFactory($configProvider);

    $handler = new CreateUserHandler($userRepository, $userFactory);

    $command = new CreateUserCommand(
      email: 'test@example.com',
      password: 'password123',
      username: 'testuser',
      displayName: 'Test User',
    );

    $handler($command);
  }

  #[Test]
  public function itDerivesInactiveStatusWhenApprovalRequired(): void {
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->willReturnMap([
        ['user.allowRegistration', true],
        ['user.approvalRequired', true]
      ]);

    $userRepository = $this->createMock(UserStoreRepositoryInterface::class);
    $userRepository->expects($this->once())
      ->method('store')
      ->with($this->callback(function (User $user) {
        return $user->getStatus() === UserStatus::Inactive;
      }));

    $userFactory = $this->createUserFactory($configProvider);

    $handler = new CreateUserHandler($userRepository, $userFactory);

    $command = new CreateUserCommand(
      email: 'test@example.com',
      password: 'password123',
      username: 'testuser',
      displayName: 'Test User',
    );

    $handler($command);
  }

  #[Test]
  public function itStoresUserWithCorrectCredentials(): void {
    $userRepository = $this->createMock(UserStoreRepositoryInterface::class);
    $userRepository->expects($this->once())
      ->method('store')
      ->with($this->callback(function (User $user) {
        return $user->getUsername()->toString() === 'testuser';
      }));

    $userFactory = $this->createUserFactory();

    $handler = new CreateUserHandler($userRepository, $userFactory);

    $command = new CreateUserCommand(
      email: 'test@example.com',
      password: 'password123',
      username: 'testuser',
      displayName: 'Test User',
      status: UserStatus::Active,
    );

    $handler($command);
  }

  #[Test]
  public function itThrowsWhenRegistrationNotAllowedAndStatusIsNull(): void {
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->willReturnMap([
        ['user.allowRegistration', false],
      ]);

    $userRepository = $this->createMock(UserStoreRepositoryInterface::class);
    $userRepository->expects($this->never())->method('store');

    $userFactory = $this->createUserFactory($configProvider);

    $handler = new CreateUserHandler($userRepository, $userFactory);

    $command = new CreateUserCommand(
      email: 'test@example.com',
      password: 'password123',
      username: 'testuser',
      displayName: 'Test User',
    );

    $this->expectException(RegistrationIsNotAllowed::class);

    $handler($command);
  }

  #[Test]
  public function itBypassesRegistrationCheckWithExplicitStatus(): void {
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->willReturnMap([
        ['user.allowRegistration', false],
      ]);

    $userRepository = $this->createMock(UserStoreRepositoryInterface::class);
    $userRepository->expects($this->once())
      ->method('store')
      ->with($this->callback(function (User $user) {
        return $user->getStatus() === UserStatus::Active;
      }));

    $userFactory = $this->createUserFactory($configProvider);

    $handler = new CreateUserHandler($userRepository, $userFactory);

    $command = new CreateUserCommand(
      email: 'test@example.com',
      password: 'password123',
      username: 'testuser',
      displayName: 'Test User',
      status: UserStatus::Active,
    );

    $handler($command);
  }

  private function createUserFactory(?ConfigurationProviderInterface $configProvider = null): UserFactory {
    $configProvider ??= $this->createStub(ConfigurationProviderInterface::class);

    $uniqueEmailSpec = $this->createStub(UniqueEmailSpecificationInterface::class);
    $uniqueEmailSpec->method('isUnique')->willReturn(true);

    $uniqueUsernameSpec = $this->createStub(UniqueUsernameSpecificationInterface::class);
    $uniqueUsernameSpec->method('isUnique')->willReturn(true);

    $uniqueDisplayNameSpec = $this->createStub(UniqueDisplayNameSpecificationInterface::class);
    $uniqueDisplayNameSpec->method('isUnique')->willReturn(true);

    $context = new UserCreationContext($uniqueEmailSpec, $uniqueUsernameSpec, $uniqueDisplayNameSpec);

    return new UserFactory($configProvider, $context);
  }
}
