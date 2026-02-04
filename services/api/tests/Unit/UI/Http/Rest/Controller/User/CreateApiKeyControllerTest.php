<?php

declare(strict_types=1);

namespace Unit\UI\Http\Rest\Controller\User;

use PHPUnit\Framework\TestCase;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\User\Application\Command\CreateApiKey\CreateApiKeyCommand;
use Slink\User\Infrastructure\Auth\JwtUser;
use UI\Http\Rest\Controller\User\CreateApiKeyController;
use UI\Http\Rest\Response\ApiResponse;

final class CreateApiKeyControllerTest extends TestCase {
  public function testItCreatesApiKeySuccessfully(): void {
    $keyName = 'Test Key';
    $expiresAt = '2025-12-31 23:59:59';
    $userId = 'user-123';
    $generatedKey = 'sk_generated_key_12345';
    
    $commandBus = $this->createMock(CommandBusInterface::class);
    $commandBus->expects($this->once())
      ->method('handleSync')
      ->willReturn($generatedKey);
    
    $controller = new CreateApiKeyController();
    
    $reflection = new \ReflectionClass($controller);
    $property = $reflection->getProperty('commandBus');

    $property->setValue($controller, $commandBus);
    
    $command = new CreateApiKeyCommand($keyName, $expiresAt);
    
    $user = $this->createStub(JwtUser::class);
    $user->method('getIdentifier')->willReturn($userId);
    
    $response = $controller->__invoke($command, $user);
    
    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());
  }

  public function testItCreatesApiKeyWithoutExpirationDate(): void {
    $keyName = 'Permanent Key';
    $userId = 'user-456';
    $generatedKey = 'sk_permanent_key_67890';
    
    $commandBus = $this->createMock(CommandBusInterface::class);
    $commandBus->expects($this->once())
      ->method('handleSync')
      ->willReturn($generatedKey);
    
    $controller = new CreateApiKeyController();
    
    $reflection = new \ReflectionClass($controller);
    $property = $reflection->getProperty('commandBus');

    $property->setValue($controller, $commandBus);
    
    $command = new CreateApiKeyCommand($keyName);
    
    $user = $this->createStub(JwtUser::class);
    $user->method('getIdentifier')->willReturn($userId);
    
    $response = $controller->__invoke($command, $user);
    
    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());
  }
}