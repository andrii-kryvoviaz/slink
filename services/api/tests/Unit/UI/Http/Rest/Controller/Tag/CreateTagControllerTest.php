<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Tag;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Application\Command\CreateTag\CreateTagCommand;
use Slink\User\Domain\Contracts\UserInterface;
use UI\Http\Rest\Controller\Tag\CreateTagController;
use UI\Http\Rest\Response\ApiResponse;

final class CreateTagControllerTest extends TestCase {

  #[Test]
  public function itCreatesTagSuccessfully(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(UserInterface::class);
    $tagId = ID::generate();
    
    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn('user-123');

    $commandBus->expects($this->once())
      ->method('handleSync')
      ->willReturn($tagId);

    $controller = new CreateTagController();
    $controller->setCommandBus($commandBus);

    $command = new CreateTagCommand('test-tag');

    $response = $controller($command, $user);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(201, $response->getStatusCode());
  }

  #[Test]
  public function itCreatesNestedTagSuccessfully(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(UserInterface::class);
    $tagId = ID::generate();
    $parentId = ID::generate();
    
    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn('user-123');

    $commandBus->expects($this->once())
      ->method('handleSync')
      ->willReturn($tagId);

    $controller = new CreateTagController();
    $controller->setCommandBus($commandBus);

    $command = new CreateTagCommand('child-tag', $parentId->toString());

    $response = $controller($command, $user);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(201, $response->getStatusCode());
  }

  #[Test]
  public function itHandlesCommandWithUserContext(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(UserInterface::class);
    $tagId = ID::generate();
    
    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn('user-456');

    $commandBus->expects($this->once())
      ->method('handleSync')
      ->with($this->isInstanceOf(\Symfony\Component\Messenger\Envelope::class))
      ->willReturn($tagId);

    $controller = new CreateTagController();
    $controller->setCommandBus($commandBus);

    $command = new CreateTagCommand('another-tag');

    $response = $controller($command, $user);

    $this->assertEquals(201, $response->getStatusCode());
  }
}