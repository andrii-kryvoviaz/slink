<?php

declare(strict_types=1);

namespace Unit\Slink\User\Application\Command\SignIn;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\User\Application\Command\SignIn\SignInCommand;
use Slink\User\Application\Command\SignIn\SignInHandler;
use Slink\User\Domain\Contracts\AuthProviderInterface;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Exception\InvalidCredentialsException;
use Slink\User\Domain\RefreshTokenSet;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Auth\TokenPair;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Username;
use Tests\Traits\PrivatePropertyTrait;

final class SignInHandlerTest extends TestCase {
  use PrivatePropertyTrait;

  /**
   * @throws DateTimeException
   */
  #[Test]
  public function itCallsUserSignInMethod(): void {
    $user = $this->createMockUser();
    $user->expects($this->once())
      ->method('signIn')
      ->with('password123');

    $tokenPair = $this->createMockTokenPair();

    $userStore = $this->createMock(UserStoreRepositoryInterface::class);
    $userStore->method('getByUsername')->willReturn($user);
    $userStore->method('store');

    $authProvider = $this->createMock(AuthProviderInterface::class);
    $authProvider->method('generateTokenPair')->willReturn($tokenPair);

    $handler = new SignInHandler($userStore, $authProvider);

    $command = new SignInCommand('testuser', 'password123');
    $handler($command);
  }

  /**
   * @throws DateTimeException
   */
  #[Test]
  public function itSignsInUserSuccessfullyWithEmail(): void {
    $user = $this->createMockUser();
    $tokenPair = $this->createMockTokenPair();

    $userStore = $this->createMock(UserStoreRepositoryInterface::class);
    $userStore->expects($this->once())
      ->method('getByUsername')
      ->with($this->isInstanceOf(Email::class))
      ->willReturn($user);
    $userStore->expects($this->once())
      ->method('store')
      ->with($user);

    $authProvider = $this->createMock(AuthProviderInterface::class);
    $authProvider->expects($this->once())
      ->method('generateTokenPair')
      ->with($user)
      ->willReturn($tokenPair);

    $handler = new SignInHandler($userStore, $authProvider);

    $command = new SignInCommand('test@example.com', 'password123');
    $result = $handler($command);

    $this->assertSame($tokenPair, $result);
  }

  /**
   * @throws DateTimeException
   */
  #[Test]
  public function itSignsInUserSuccessfullyWithUsername(): void {
    $user = $this->createMockUser();
    $tokenPair = $this->createMockTokenPair();

    $userStore = $this->createMock(UserStoreRepositoryInterface::class);
    $userStore->expects($this->once())
      ->method('getByUsername')
      ->with($this->isInstanceOf(Username::class))
      ->willReturn($user);
    $userStore->expects($this->once())
      ->method('store')
      ->with($user);

    $authProvider = $this->createMock(AuthProviderInterface::class);
    $authProvider->expects($this->once())
      ->method('generateTokenPair')
      ->with($user)
      ->willReturn($tokenPair);

    $handler = new SignInHandler($userStore, $authProvider);

    $command = new SignInCommand('testuser', 'password123');
    $result = $handler($command);

    $this->assertSame($tokenPair, $result);
  }

  /**
   * @throws DateTimeException
   */
  #[Test]
  public function itThrowsExceptionWhenUserIsBanned(): void {
    $this->expectException(InvalidCredentialsException::class);

    $user = $this->createMockUser(UserStatus::Banned);

    $userStore = $this->createMock(UserStoreRepositoryInterface::class);
    $userStore->method('getByUsername')->willReturn($user);

    $authProvider = $this->createMock(AuthProviderInterface::class);

    $handler = new SignInHandler($userStore, $authProvider);

    $command = new SignInCommand('testuser', 'password123');
    $handler($command);
  }

  /**
   * @throws DateTimeException
   */
  #[Test]
  public function itThrowsExceptionWhenUserIsDeleted(): void {
    $this->expectException(InvalidCredentialsException::class);

    $user = $this->createMockUser(UserStatus::Deleted);

    $userStore = $this->createMock(UserStoreRepositoryInterface::class);
    $userStore->method('getByUsername')->willReturn($user);

    $authProvider = $this->createMock(AuthProviderInterface::class);

    $handler = new SignInHandler($userStore, $authProvider);

    $command = new SignInCommand('testuser', 'password123');
    $handler($command);
  }

  /**
   * @throws DateTimeException
   */
  #[Test]
  public function itThrowsExceptionWhenUserIsInactive(): void {
    $this->expectException(InvalidCredentialsException::class);

    $user = $this->createMockUser(UserStatus::Inactive);

    $userStore = $this->createMock(UserStoreRepositoryInterface::class);
    $userStore->method('getByUsername')->willReturn($user);

    $authProvider = $this->createMock(AuthProviderInterface::class);

    $handler = new SignInHandler($userStore, $authProvider);

    $command = new SignInCommand('testuser', 'password123');
    $handler($command);
  }

  /**
   * @throws DateTimeException
   */
  #[Test]
  public function itThrowsExceptionWhenUserIsSuspended(): void {
    $this->expectException(InvalidCredentialsException::class);

    $user = $this->createMockUser(UserStatus::Suspended);

    $userStore = $this->createMock(UserStoreRepositoryInterface::class);
    $userStore->method('getByUsername')->willReturn($user);

    $authProvider = $this->createMock(AuthProviderInterface::class);

    $handler = new SignInHandler($userStore, $authProvider);

    $command = new SignInCommand('testuser', 'password123');
    $handler($command);
  }

  #[Test]
  public function itThrowsExceptionWhenUserNotFound(): void {
    $this->expectException(InvalidCredentialsException::class);

    $userStore = $this->createMock(UserStoreRepositoryInterface::class);
    $userStore->method('getByUsername')->willReturn(null);

    $authProvider = $this->createMock(AuthProviderInterface::class);

    $handler = new SignInHandler($userStore, $authProvider);

    $command = new SignInCommand('nonexistent', 'password123');
    $handler($command);
  }

  private function createMockTokenPair(): TokenPair {
    $tokenPair = $this->createMock(TokenPair::class);
    $futureTimestamp = time() + 3600;
    $tokenPair->method('getRefreshToken')->willReturn('550e8400-e29b-41d4-a716-446655440000.' . $futureTimestamp);
    $tokenPair->method('getAccessToken')->willReturn('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9');
    return $tokenPair;
  }

  private function createMockUser(UserStatus $status = UserStatus::Active): mixed {
    $user = $this->createMock(User::class);
    $user->method('getStatus')->willReturn($status);

    $refreshTokenSet = $this->createMock(RefreshTokenSet::class);
    $this->setPrivateProperty($user, 'refreshToken', $refreshTokenSet);

    return $user;
  }
}
