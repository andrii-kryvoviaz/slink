<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Tag;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Tag\Application\Command\DeleteTag\DeleteTagCommand;
use Slink\User\Domain\Contracts\UserInterface;
use UI\Http\Rest\Controller\Tag\DeleteTagController;
use UI\Http\Rest\Response\ApiResponse;

final class DeleteTagControllerTest extends TestCase {

  #[Test]
  public function itDeletesTagSuccessfully(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(UserInterface::class);
    
    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn('user-123');

    $commandBus->expects($this->once())
      ->method('handle')
      ->with($this->isInstanceOf(\Symfony\Component\Messenger\Envelope::class));

    $controller = new DeleteTagController();
    $controller->setCommandBus($commandBus);

    $response = $controller('tag-123', $user);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(204, $response->getStatusCode());
  }

  #[Test]
  public function itCreatesDeleteCommandWithCorrectId(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(UserInterface::class);
    
    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn('user-456');

    $commandBus->expects($this->once())
      ->method('handle')
      ->with($this->callback(function ($envelope) {
        return $envelope instanceof \Symfony\Component\Messenger\Envelope 
          && $envelope->getMessage() instanceof DeleteTagCommand;
      }));

    $controller = new DeleteTagController();
    
    $reflection = new \ReflectionClass($controller);
    $property = $reflection->getProperty('commandBus');

    $property->setValue($controller, $commandBus);

    $response = $controller('tag-456', $user);

    $this->assertEquals(204, $response->getStatusCode());
  }

  #[Test]
  public function itReturnsEmptyResponse(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(UserInterface::class);
    
    $user->method('getIdentifier')->willReturn('user-789');
    $commandBus->method('handle');

    $controller = new DeleteTagController();
    $controller->setCommandBus($commandBus);

    $response = $controller('tag-789', $user);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(204, $response->getStatusCode());
  }
}