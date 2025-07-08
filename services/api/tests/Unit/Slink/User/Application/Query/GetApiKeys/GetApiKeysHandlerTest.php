<?php

declare(strict_types=1);

namespace Unit\Slink\User\Application\Query\GetApiKeys;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Application\Query\GetApiKeys\GetApiKeysHandler;
use Slink\User\Application\Query\GetApiKeys\GetApiKeysQuery;
use Slink\User\Domain\Repository\ApiKeyRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\ApiKeyView;

final class GetApiKeysHandlerTest extends TestCase {
  private ApiKeyRepositoryInterface&MockObject $apiKeyRepository;
  private GetApiKeysHandler $handler;

  protected function setUp(): void {
    $this->apiKeyRepository = $this->createMock(ApiKeyRepositoryInterface::class);
    $this->handler = new GetApiKeysHandler($this->apiKeyRepository);
  }

  public function testItReturnsApiKeysForUser(): void {
    $userId = ID::generate();
    $query = new GetApiKeysQuery($userId);
    
    $apiKey1 = $this->createMock(ApiKeyView::class);
    $apiKey2 = $this->createMock(ApiKeyView::class);
    
    $apiKey1Payload = ['id' => 'key-1', 'name' => 'Key 1'];
    $apiKey2Payload = ['id' => 'key-2', 'name' => 'Key 2'];
    
    $apiKey1->method('toPayload')->willReturn($apiKey1Payload);
    $apiKey2->method('toPayload')->willReturn($apiKey2Payload);
    
    $this->apiKeyRepository
      ->expects($this->once())
      ->method('findByUserId')
      ->with($userId)
      ->willReturn([$apiKey1, $apiKey2]);
    
    $result = $this->handler->__invoke($query);
    
    $this->assertEquals([$apiKey1Payload, $apiKey2Payload], $result);
  }

  public function testItReturnsEmptyArrayWhenNoApiKeys(): void {
    $userId = ID::generate();
    $query = new GetApiKeysQuery($userId);
    
    $this->apiKeyRepository
      ->expects($this->once())
      ->method('findByUserId')
      ->with($userId)
      ->willReturn([]);
    
    $result = $this->handler->__invoke($query);
    
    $this->assertEquals([], $result);
  }
}
