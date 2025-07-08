<?php

declare(strict_types=1);

namespace Unit\Slink\User\Infrastructure\Auth;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\User\Domain\Repository\ApiKeyRepositoryInterface;
use Slink\User\Infrastructure\Auth\ApiKeyUser;
use Slink\User\Infrastructure\Auth\ApiKeyUserProvider;
use Slink\User\Infrastructure\ReadModel\View\ApiKeyView;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

final class ApiKeyUserProviderTest extends TestCase {
  private ApiKeyRepositoryInterface&MockObject $apiKeyRepository;
  private ApiKeyUserProvider $userProvider;

  protected function setUp(): void {
    $this->apiKeyRepository = $this->createMock(ApiKeyRepositoryInterface::class);
    $this->userProvider = new ApiKeyUserProvider($this->apiKeyRepository);
  }

  public function testItLoadsUserByValidApiKey(): void {
    $apiKey = 'sk_test_key_12345';
    $userId = 'user-123';
    $keyId = 'key-456';
    
    $apiKeyView = $this->createMock(ApiKeyView::class);
    $apiKeyView->method('getUserId')->willReturn($userId);
    $apiKeyView->method('getKeyId')->willReturn($keyId);
    $apiKeyView->method('isExpired')->willReturn(false);
    
    $this->apiKeyRepository
      ->expects($this->once())
      ->method('findByKey')
      ->with($apiKey)
      ->willReturn($apiKeyView);
    
    $apiKeyView
      ->expects($this->once())
      ->method('updateLastUsed');
    
    $this->apiKeyRepository
      ->expects($this->once())
      ->method('save')
      ->with($apiKeyView);
    
    $user = $this->userProvider->loadUserByIdentifier($apiKey);
    
    $this->assertInstanceOf(ApiKeyUser::class, $user);
    $this->assertEquals($userId, $user->getIdentifier());
    $this->assertEquals($keyId, $user->getKeyId());
  }

  public function testItThrowsExceptionWhenApiKeyNotFound(): void {
    $this->expectException(UserNotFoundException::class);
    $this->expectExceptionMessage('API key not found or expired');
    
    $apiKey = 'sk_nonexistent_key';
    
    $this->apiKeyRepository
      ->expects($this->once())
      ->method('findByKey')
      ->with($apiKey)
      ->willReturn(null);
    
    $this->userProvider->loadUserByIdentifier($apiKey);
  }

  public function testItThrowsExceptionWhenApiKeyExpired(): void {
    $this->expectException(UserNotFoundException::class);
    $this->expectExceptionMessage('API key not found or expired');
    
    $apiKey = 'sk_expired_key';
    
    $apiKeyView = $this->createMock(ApiKeyView::class);
    $apiKeyView->method('isExpired')->willReturn(true);
    
    $this->apiKeyRepository
      ->expects($this->once())
      ->method('findByKey')
      ->with($apiKey)
      ->willReturn($apiKeyView);
    
    $this->userProvider->loadUserByIdentifier($apiKey);
  }

  public function testItRefreshesApiKeyUser(): void {
    $userId = 'user-123';
    $keyId = 'key-456';
    
    $apiKeyUser = $this->createMock(ApiKeyUser::class);
    $apiKeyUser->method('getIdentifier')->willReturn($userId);
    
    $apiKeyView = $this->createMock(ApiKeyView::class);
    $apiKeyView->method('getUserId')->willReturn($userId);
    $apiKeyView->method('getKeyId')->willReturn($keyId);
    $apiKeyView->method('isExpired')->willReturn(false);
    
    $this->apiKeyRepository
      ->method('findByKey')
      ->with($userId)
      ->willReturn($apiKeyView);
    
    $refreshedUser = $this->userProvider->refreshUser($apiKeyUser);
    
    $this->assertInstanceOf(ApiKeyUser::class, $refreshedUser);
  }

  public function testItThrowsExceptionWhenRefreshingUnsupportedUser(): void {
    $this->expectException(UnsupportedUserException::class);
    
    $user = $this->createMock(UserInterface::class);
    
    $this->userProvider->refreshUser($user);
  }

  public function testItSupportsApiKeyUserClass(): void {
    $this->assertTrue($this->userProvider->supportsClass(ApiKeyUser::class));
  }

  public function testItDoesNotSupportOtherUserClasses(): void {
    $this->assertFalse($this->userProvider->supportsClass(UserInterface::class));
    $this->assertFalse($this->userProvider->supportsClass('SomeOtherUserClass'));
  }
}
