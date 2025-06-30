<?php

declare(strict_types=1);

namespace Unit\Slink\User\Application\Command\SignUp;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\User\Application\Command\SignUp\SignUpCommand;
use Slink\User\Application\Command\SignUp\SignUpHandler;
use Slink\User\Domain\Context\UserCreationContext;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Exception\RegistrationIsNotAllowed;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\Specification\UniqueDisplayNameSpecificationInterface;
use Slink\User\Domain\Specification\UniqueEmailSpecificationInterface;
use Slink\User\Domain\Specification\UniqueUsernameSpecificationInterface;
use Slink\User\Domain\User;

final class SignUpHandlerTest extends TestCase {

  /**
   * @throws DateTimeException
   */
  #[Test]
  public function itCreatesActiveUserWhenApprovalNotRequired(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
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

    $userCreationContext = $this->createUserCreationContext();

    $handler = new SignUpHandler($configProvider, $userRepository, $userCreationContext);

    $command = new SignUpCommand(
      'test@example.com',
      'password123',
      'password123',
      'testuser'
    );

    $handler($command);
  }

  /**
   * @throws DateTimeException
   */
  #[Test]
  public function itCreatesInactiveUserWhenApprovalRequired(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
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

    $userCreationContext = $this->createUserCreationContext();

    $handler = new SignUpHandler($configProvider, $userRepository, $userCreationContext);

    $command = new SignUpCommand(
      'test@example.com',
      'password123',
      'password123',
      'testuser'
    );

    $handler($command);
  }

  /**
   * @throws DateTimeException
   */
  #[Test]
  public function itHandlesSignUpSuccessfullyWhenRegistrationAllowed(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->willReturnMap([
        ['user.allowRegistration', true],
        ['user.approvalRequired', false]
      ]);

    $userRepository = $this->createMock(UserStoreRepositoryInterface::class);
    $userRepository->expects($this->once())
      ->method('store')
      ->with($this->isInstanceOf(User::class));

    $userCreationContext = $this->createUserCreationContext();

    $handler = new SignUpHandler($configProvider, $userRepository, $userCreationContext);

    $command = new SignUpCommand(
      'test@example.com',
      'password123',
      'password123',
      'testuser'
    );

    $handler($command);
  }

  /**
   * @throws DateTimeException
   */
  #[Test]
  public function itStoresUserWithCorrectCredentials(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->willReturnMap([
        ['user.allowRegistration', true],
        ['user.approvalRequired', false]
      ]);

    $userRepository = $this->createMock(UserStoreRepositoryInterface::class);
    $userRepository->expects($this->once())
      ->method('store')
      ->with($this->callback(function (User $user) {
        return $user->getUsername()->toString() === 'testuser';
      }));

    $userCreationContext = $this->createUserCreationContext();

    $handler = new SignUpHandler($configProvider, $userRepository, $userCreationContext);

    $command = new SignUpCommand(
      'test@example.com',
      'password123',
      'password123',
      'testuser'
    );

    $handler($command);
  }

  #[Test]
  public function itThrowsExceptionWhenRegistrationNotAllowed(): void {
    $this->expectException(RegistrationIsNotAllowed::class);

    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->with('user.allowRegistration')
      ->willReturn(false);

    $userRepository = $this->createMock(UserStoreRepositoryInterface::class);
    $userRepository->expects($this->never())->method('store');

    $userCreationContext = $this->createUserCreationContext();

    $handler = new SignUpHandler($configProvider, $userRepository, $userCreationContext);

    $command = new SignUpCommand(
      'test@example.com',
      'password123',
      'password123',
      'testuser'
    );

    $handler($command);
  }

  private function createUserCreationContext(): UserCreationContext {
    $uniqueEmailSpec = $this->createMock(UniqueEmailSpecificationInterface::class);
    $uniqueEmailSpec->method('isUnique')->willReturn(true);

    $uniqueUsernameSpec = $this->createMock(UniqueUsernameSpecificationInterface::class);
    $uniqueUsernameSpec->method('isUnique')->willReturn(true);

    $uniqueDisplayNameSpec = $this->createMock(UniqueDisplayNameSpecificationInterface::class);
    $uniqueDisplayNameSpec->method('isUnique')->willReturn(true);

    return new UserCreationContext($uniqueEmailSpec, $uniqueUsernameSpec, $uniqueDisplayNameSpec);
  }
}
