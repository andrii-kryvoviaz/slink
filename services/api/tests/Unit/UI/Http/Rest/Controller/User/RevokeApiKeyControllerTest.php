<?php

declare(strict_types=1);

namespace Unit\UI\Http\Rest\Controller\User;

use PHPUnit\Framework\TestCase;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\User\Infrastructure\Auth\JwtUser;
use UI\Http\Rest\Controller\User\RevokeApiKeyController;
use UI\Http\Rest\Response\ApiResponse;

final class RevokeApiKeyControllerTest extends TestCase {
  public function testItRevokesApiKeySuccessfully(): void {
    $keyId = 'key-123';
    $userId = 'user-456';
    
    $commandBus = $this->createMock(CommandBusInterface::class);
    $commandBus->expects($this->once())
      ->method('handle');
    
    $controller = new RevokeApiKeyController();
    
    $reflection = new \ReflectionClass($controller);
    $property = $reflection->getProperty('commandBus');
    $property->setAccessible(true);
    $property->setValue($controller, $commandBus);
    
    $user = $this->createMock(JwtUser::class);
    $user->method('getIdentifier')->willReturn($userId);
    
    $response = $controller->__invoke($keyId, $user);
    
    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(204, $response->getStatusCode());
  }
}