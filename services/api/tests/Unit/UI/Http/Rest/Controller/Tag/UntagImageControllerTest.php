<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Tag;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Command\UntagImage\UntagImageCommand;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\User\Domain\Contracts\UserInterface;
use UI\Http\Rest\Controller\Tag\UntagImageController;
use UI\Http\Rest\Response\ApiResponse;

final class UntagImageControllerTest extends TestCase {

  #[Test]
  public function itUntagsImageSuccessfully(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(UserInterface::class);
    
    $user->method('getIdentifier')->willReturn('user-789');
    $commandBus->expects($this->once())->method('handle');

    $controller = new UntagImageController();
    $controller->setCommandBus($commandBus);

    $response = $controller('image-123', 'tag-456', $user);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(204, $response->getStatusCode());
  }

  #[Test]
  public function itCreatesUntagImageCommandWithCorrectIds(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(UserInterface::class);
    
    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn('user-456');

    $commandBus->expects($this->once())
      ->method('handle')
      ->with($this->callback(function ($envelope) {
        $message = $envelope->getMessage();
        return $envelope instanceof \Symfony\Component\Messenger\Envelope 
          && $message instanceof UntagImageCommand
          && $message->getImageId() === 'image-789'
          && $message->getTagId() === 'tag-abc';
      }));

    $controller = new UntagImageController();
    
    $reflection = new \ReflectionClass($controller);
    $property = $reflection->getProperty('commandBus');
    $property->setAccessible(true);
    $property->setValue($controller, $commandBus);

    $response = $controller('image-789', 'tag-abc', $user);

    $this->assertEquals(204, $response->getStatusCode());
  }

  #[Test]
  public function itReturnsEmptyResponse(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(UserInterface::class);
    
    $user->method('getIdentifier')->willReturn('user-empty');
    $commandBus->method('handle');

    $controller = new UntagImageController();
    
    $reflection = new \ReflectionClass($controller);
    $property = $reflection->getProperty('commandBus');
    $property->setAccessible(true);
    $property->setValue($controller, $commandBus);

    $response = $controller('image-empty', 'tag-empty', $user);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(204, $response->getStatusCode());
  }

  #[Test]
  public function itHandlesUserContextCorrectly(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(UserInterface::class);
    
    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn('specific-user-id');

    $commandBus->expects($this->once())
      ->method('handle');

    $controller = new UntagImageController();
    
    $reflection = new \ReflectionClass($controller);
    $property = $reflection->getProperty('commandBus');
    $property->setAccessible(true);
    $property->setValue($controller, $commandBus);

    $response = $controller('image-test', 'tag-test', $user);

    $this->assertEquals(204, $response->getStatusCode());
  }
}