<?php

declare(strict_types=1);

namespace Unit\Slink\User\Application\Command\SsoSignIn;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\User\Application\Command\SsoSignIn\SsoSignInCommand;
use Slink\User\Application\Command\SsoSignIn\SsoSignInHandler;
use Slink\User\Application\Service\OAuthUserResolverInterface;
use Slink\User\Domain\Contracts\AuthProviderInterface;
use Slink\User\Domain\Contracts\OAuthAdapterInterface;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Exception\InvalidCredentialsException;
use Slink\User\Domain\Exception\UserPendingApprovalException;
use Slink\User\Domain\RefreshTokenSet;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Auth\TokenPair;
use Slink\User\Domain\ValueObject\OAuth\OAuthIdentity;
use Tests\Traits\PrivatePropertyTrait;

final class SsoSignInHandlerTest extends TestCase {
  use PrivatePropertyTrait;

  #[Test]
  public function itAuthenticatesOnHappyPath(): void {
    $identity = $this->createMockIdentity();
    $tokenPair = $this->createMockTokenPair();

    $user = $this->createMock(User::class);
    $user->method('getStatus')->willReturn(UserStatus::Active);
    $user->method('getIdentifier')->willReturn('user-id-123');
    $user->expects($this->once())->method('link')->with($identity);
    $user->expects($this->once())->method('authenticate');

    $refreshTokenSet = $this->createStub(RefreshTokenSet::class);
    $this->setPrivateProperty($user, 'refreshToken', $refreshTokenSet);

    [$oauthAdapter, $userResolver, $userStore, $authProvider] = $this->createMockDependencies($identity, $user);

    $userStore->expects($this->exactly(2))
      ->method('store')
      ->with($user);

    $authProvider->expects($this->once())
      ->method('generateTokenPair')
      ->with($user)
      ->willReturn($tokenPair);

    $handler = new SsoSignInHandler($oauthAdapter, $userResolver, $userStore, $authProvider);

    $command = new SsoSignInCommand('auth-code-xyz', 'state-abc');

    $result = $handler($command);

    $this->assertSame($tokenPair, $result);
  }

  #[Test]
  public function itThrowsWhenUserPendingApproval(): void {
    $identity = $this->createMockIdentity();

    $user = $this->createStub(User::class);
    $user->method('getStatus')->willReturn(UserStatus::Inactive);
    $user->method('getIdentifier')->willReturn('user-id-456');

    [$oauthAdapter, $userResolver, $userStore, $authProvider] = $this->createMockDependencies($identity, $user);

    $userStore->expects($this->once())->method('store')->with($user);

    $authProvider->expects($this->never())->method('generateTokenPair');

    $handler = new SsoSignInHandler($oauthAdapter, $userResolver, $userStore, $authProvider);

    $command = new SsoSignInCommand('auth-code-xyz', 'state-abc');

    $this->expectException(UserPendingApprovalException::class);

    $handler($command);
  }

  #[Test]
  public function itThrowsWhenUserRestricted(): void {
    $identity = $this->createMockIdentity();

    $user = $this->createStub(User::class);
    $user->method('getStatus')->willReturn(UserStatus::Banned);
    $user->method('getIdentifier')->willReturn('user-id-789');

    [$oauthAdapter, $userResolver, $userStore, $authProvider] = $this->createMockDependencies($identity, $user);

    $userStore->expects($this->once())->method('store')->with($user);

    $authProvider->expects($this->never())->method('generateTokenPair');

    $handler = new SsoSignInHandler($oauthAdapter, $userResolver, $userStore, $authProvider);

    $command = new SsoSignInCommand('auth-code-xyz', 'state-abc');

    $this->expectException(InvalidCredentialsException::class);

    $handler($command);
  }

  private function createMockIdentity(): OAuthIdentity {
    return $this->createStub(OAuthIdentity::class);
  }

  /**
   * @return array{OAuthAdapterInterface, OAuthUserResolverInterface, MockObject&UserStoreRepositoryInterface, MockObject&AuthProviderInterface}
   */
  private function createMockDependencies(OAuthIdentity $identity, User $user): array {
    $oauthAdapter = $this->createStub(OAuthAdapterInterface::class);
    $oauthAdapter->method('exchangeCode')->willReturn($identity);

    $userResolver = $this->createStub(OAuthUserResolverInterface::class);
    $userResolver->method('resolve')->willReturn($user);

    $userStore = $this->createMock(UserStoreRepositoryInterface::class);

    $authProvider = $this->createMock(AuthProviderInterface::class);

    return [$oauthAdapter, $userResolver, $userStore, $authProvider];
  }

  private function createMockTokenPair(): TokenPair {
    $tokenPair = $this->createStub(TokenPair::class);
    $futureTimestamp = time() + 3600;
    $tokenPair->method('getRefreshToken')->willReturn('550e8400-e29b-41d4-a716-446655440000.' . $futureTimestamp);
    $tokenPair->method('getAccessToken')->willReturn('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9');
    return $tokenPair;
  }
}
