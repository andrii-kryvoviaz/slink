<?php

declare(strict_types=1);

namespace Unit\Slink\User\Infrastructure\Auth;

use PHPUnit\Framework\TestCase;
use Slink\User\Infrastructure\Auth\ApiKeyUser;
use Slink\User\Infrastructure\ReadModel\View\ApiKeyView;

final class ApiKeyUserTest extends TestCase {
  public function testItCreatesFromApiKeyView(): void {
    $userId = 'user-123';
    $keyId = 'key-456';
    
    $apiKeyView = $this->createMock(ApiKeyView::class);
    $apiKeyView->method('getUserId')->willReturn($userId);
    $apiKeyView->method('getKeyId')->willReturn($keyId);
    
    $apiKeyUser = ApiKeyUser::fromApiKey($apiKeyView);
    
    $this->assertEquals($userId, $apiKeyUser->getIdentifier());
    $this->assertEquals($userId, $apiKeyUser->getUserIdentifier());
    $this->assertEquals($keyId, $apiKeyUser->getKeyId());
    $this->assertEquals(['ROLE_USER'], $apiKeyUser->getRoles());
  }

  public function testItErasesCredentials(): void {
    $apiKeyView = $this->createMock(ApiKeyView::class);
    $apiKeyView->method('getUserId')->willReturn('user-123');
    $apiKeyView->method('getKeyId')->willReturn('key-456');
    
    $apiKeyUser = ApiKeyUser::fromApiKey($apiKeyView);
    
    $apiKeyUser->eraseCredentials();
    
    $this->assertEquals('user-123', $apiKeyUser->getIdentifier());
    $this->assertEquals('key-456', $apiKeyUser->getKeyId());
  }
}
