<?php

declare(strict_types=1);

namespace Unit\UI\Http\Rest\Controller\User;

use PHPUnit\Framework\TestCase;
use Slink\Shared\Application\Query\QueryBusInterface;
use Slink\User\Infrastructure\Auth\JwtUser;
use UI\Http\Rest\Controller\User\GetApiKeysController;
use UI\Http\Rest\Response\ApiResponse;

final class GetApiKeysControllerTest extends TestCase {
  public function testItReturnsApiKeysSuccessfully(): void {
    $userId = 'user-123';
    
    $queryBus = $this->createMock(QueryBusInterface::class);
    $apiKeysData = [
      ['id' => 'key-1', 'name' => 'Key 1'],
      ['id' => 'key-2', 'name' => 'Key 2']
    ];
    
    $queryBus->expects($this->once())
      ->method('ask')
      ->willReturn($apiKeysData);
    
    $controller = new GetApiKeysController();
    
    $reflection = new \ReflectionClass($controller);
    $property = $reflection->getProperty('queryBus');
    $property->setAccessible(true);
    $property->setValue($controller, $queryBus);
    
    $user = $this->createMock(JwtUser::class);
    $user->method('getIdentifier')->willReturn($userId);
    
    $response = $controller->__invoke($user);
    
    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());
  }

  public function testItReturnsEmptyApiKeysArray(): void {
    $userId = 'user-456';
    
    $queryBus = $this->createMock(QueryBusInterface::class);
    $queryBus->expects($this->once())
      ->method('ask')
      ->willReturn([]);
    
    $controller = new GetApiKeysController();
    
    $reflection = new \ReflectionClass($controller);
    $property = $reflection->getProperty('queryBus');
    $property->setAccessible(true);
    $property->setValue($controller, $queryBus);
    
    $user = $this->createMock(JwtUser::class);
    $user->method('getIdentifier')->willReturn($userId);
    
    $response = $controller->__invoke($user);
    
    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());
  }
}