<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\External;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Command\UploadImage\UploadImageCommand;
use Slink\Image\Application\Query\GetExternalUploadResponse\GetExternalUploadResponseQuery;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Application\Query\QueryBusInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Infrastructure\Auth\ApiKeyUser;
use Slink\User\Infrastructure\ReadModel\View\ApiKeyView;
use Symfony\Component\HttpFoundation\File\File;
use UI\Http\Rest\Response\ApiResponse;

final class UploadControllerTest extends TestCase {
  private CommandBusInterface&MockObject $commandBus;
  private QueryBusInterface&MockObject $queryBus;
  private UploadController $controller;
  
  protected function setUp(): void {
    $this->commandBus = $this->createMock(CommandBusInterface::class);
    $this->queryBus = $this->createMock(QueryBusInterface::class);
    $this->controller = new UploadController();
    
    $reflection = new \ReflectionClass($this->controller);
    
    $commandProperty = $reflection->getProperty('commandBus');

    $commandProperty->setValue($this->controller, $this->commandBus);
    
    $queryProperty = $reflection->getProperty('queryBus');

    $queryProperty->setValue($this->controller, $this->queryBus);
  }
  
  #[Test]
  public function itUploadsImageWithApiKeyUserAndReturnsProperShareXResponse(): void {
    $imageId = '123e4567-e89b-12d3-a456-426614174000';
    $expectedResponse = [
      'url' => 'https://example.com/image/123e4567-e89b-12d3-a456-426614174000.jpg',
      'thumbnailUrl' => 'https://example.com/image/123e4567-e89b-12d3-a456-426614174000.jpg?width=300&height=300&crop=true',
      'id' => $imageId
    ];
    
    $mockFile = $this->createStub(File::class);
    $mockFile->method('guessExtension')->willReturn('jpg');
    
    $command = $this->createMock(UploadImageCommand::class);
    $envelope = $this->createStub(\Symfony\Component\Messenger\Envelope::class);
    
    $command->expects($this->once())
      ->method('withContext')
      ->with(['userId' => 'user-123'])
      ->willReturn($envelope);
    
    $command->expects($this->once())
      ->method('getId')
      ->willReturn(ID::fromString($imageId));
      
    $command->expects($this->once())
      ->method('getImageFile')
      ->willReturn($mockFile);
    
    $this->commandBus->expects($this->once())
      ->method('handle')
      ->with($envelope);
    
    $this->queryBus->expects($this->once())
      ->method('ask')
      ->with($this->isInstanceOf(GetExternalUploadResponseQuery::class))
      ->willReturn($expectedResponse);
    
    $apiKeyView = $this->createStub(ApiKeyView::class);
    $apiKeyView->method('getUserId')->willReturn('user-123');
    $apiKeyView->method('getKeyId')->willReturn('key-123');
    
    $user = ApiKeyUser::fromApiKey($apiKeyView);
    
    $response = ($this->controller)($command, $user);
    
    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(201, $response->getStatusCode());
    
    $responseData = \json_decode((string)$response->getContent(), true);
    $this->assertEquals($expectedResponse, $responseData);
  }
}
