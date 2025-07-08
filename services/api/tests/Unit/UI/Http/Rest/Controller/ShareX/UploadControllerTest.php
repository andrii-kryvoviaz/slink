<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\External;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Command\UploadImage\UploadImageCommand;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Infrastructure\Auth\ApiKeyUser;
use Slink\User\Infrastructure\ReadModel\View\ApiKeyView;
use UI\Http\Rest\Response\ApiResponse;

final class UploadControllerTest extends TestCase {
  private CommandBusInterface&MockObject $commandBus;
  private UploadController $controller;
  
  protected function setUp(): void {
    $this->commandBus = $this->createMock(CommandBusInterface::class);
    $this->controller = new UploadController();
    
    $reflection = new \ReflectionClass($this->controller);
    $property = $reflection->getProperty('commandBus');
    $property->setAccessible(true);
    $property->setValue($this->controller, $this->commandBus);
  }
  
  public function testItUploadsImageWithApiKeyUser(): void {
    $command = $this->createMock(UploadImageCommand::class);
    $envelope = $this->createMock(\Symfony\Component\Messenger\Envelope::class);
    
    $command->expects($this->once())
      ->method('withContext')
      ->with(['userId' => 'user-123'])
      ->willReturn($envelope);
    
    $command->expects($this->exactly(2))
      ->method('getId')
      ->willReturn(ID::fromString('image-123'));
    
    $this->commandBus->expects($this->once())
      ->method('handle')
      ->with($envelope);
    
    $apiKeyView = $this->createMock(ApiKeyView::class);
    $apiKeyView->method('getUserId')->willReturn('user-123');
    $apiKeyView->method('getKeyId')->willReturn('key-123');
    
    $user = ApiKeyUser::fromApiKey($apiKeyView);
    
    $response = ($this->controller)($command, $user);
    
    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(201, $response->getStatusCode());
  }
}
