<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Comment;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Comment\Application\Command\CreateComment\CreateCommentCommand;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\HandlerArgumentsStamp;
use UI\Http\Rest\Controller\Comment\CreateCommentController;
use UI\Http\Rest\Response\ApiResponse;

final class CreateCommentControllerTest extends TestCase {
  #[Test]
  public function itCreatesCommentSuccessfully(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(JwtUser::class);
    $imageId = 'image-123';

    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn('user-456');

    $commandBus->expects($this->once())
      ->method('handle')
      ->with($this->callback(function ($envelope) {
        return $envelope instanceof Envelope
          && $envelope->getMessage() instanceof CreateCommentCommand;
      }));

    $controller = new CreateCommentController();
    $controller->setCommandBus($commandBus);

    $command = new CreateCommentCommand('This is a test comment');

    $response = $controller($command, $user, $imageId);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(201, $response->getStatusCode());
  }

  #[Test]
  public function itCreatesReplyCommentSuccessfully(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(JwtUser::class);
    $imageId = 'image-123';
    $referencedCommentId = 'comment-789';

    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn('user-456');

    $commandBus->expects($this->once())
      ->method('handle');

    $controller = new CreateCommentController();
    $controller->setCommandBus($commandBus);

    $command = new CreateCommentCommand('This is a reply', $referencedCommentId);

    $response = $controller($command, $user, $imageId);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(201, $response->getStatusCode());
  }

  #[Test]
  public function itPassesImageIdToCommand(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(JwtUser::class);
    $imageId = 'specific-image-id';

    $user->method('getIdentifier')->willReturn('user-123');

    $commandBus->expects($this->once())
      ->method('handle')
      ->with($this->callback(function (Envelope $envelope) use ($imageId) {
        $message = $envelope->getMessage();
        if (!$message instanceof CreateCommentCommand) {
          return false;
        }
        $stamp = $envelope->last(HandlerArgumentsStamp::class);
        if (!$stamp instanceof HandlerArgumentsStamp) {
          return false;
        }
        $context = $stamp->getAdditionalArguments();
        return ($context['imageId'] ?? null) === $imageId;
      }));

    $controller = new CreateCommentController();
    $controller->setCommandBus($commandBus);

    $command = new CreateCommentCommand('Test comment');

    $controller($command, $user, $imageId);
  }

  #[Test]
  public function itPassesUserIdToCommand(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(JwtUser::class);
    $userId = 'specific-user-id';
    $imageId = 'image-123';

    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn($userId);

    $commandBus->expects($this->once())
      ->method('handle')
      ->with($this->callback(function (Envelope $envelope) use ($userId) {
        $message = $envelope->getMessage();
        if (!$message instanceof CreateCommentCommand) {
          return false;
        }
        $stamp = $envelope->last(HandlerArgumentsStamp::class);
        if (!$stamp instanceof HandlerArgumentsStamp) {
          return false;
        }
        $context = $stamp->getAdditionalArguments();
        return ($context['userId'] ?? null) === $userId;
      }));

    $controller = new CreateCommentController();
    $controller->setCommandBus($commandBus);

    $command = new CreateCommentCommand('Test comment');

    $controller($command, $user, $imageId);
  }
}
