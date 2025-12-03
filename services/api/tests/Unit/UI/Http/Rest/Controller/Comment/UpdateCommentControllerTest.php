<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Comment;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Comment\Application\Command\UpdateComment\UpdateCommentCommand;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\HandlerArgumentsStamp;
use UI\Http\Rest\Controller\Comment\UpdateCommentController;
use UI\Http\Rest\Response\ApiResponse;

final class UpdateCommentControllerTest extends TestCase {
  #[Test]
  public function itUpdatesCommentSuccessfully(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(JwtUser::class);
    $commentId = 'comment-123';

    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn('user-456');

    $commandBus->expects($this->once())
      ->method('handle')
      ->with($this->callback(function ($envelope) {
        return $envelope instanceof Envelope
          && $envelope->getMessage() instanceof UpdateCommentCommand;
      }));

    $controller = new UpdateCommentController();
    $controller->setCommandBus($commandBus);

    $command = new UpdateCommentCommand('Updated content');

    $response = $controller($command, $user, $commentId);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(204, $response->getStatusCode());
  }

  #[Test]
  public function itPassesCommentIdToCommand(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(JwtUser::class);
    $commentId = 'specific-comment-id';

    $user->method('getIdentifier')->willReturn('user-123');

    $commandBus->expects($this->once())
      ->method('handle')
      ->with($this->callback(function (Envelope $envelope) use ($commentId) {
        $message = $envelope->getMessage();
        if (!$message instanceof UpdateCommentCommand) {
          return false;
        }
        $stamp = $envelope->last(HandlerArgumentsStamp::class);
        if (!$stamp instanceof HandlerArgumentsStamp) {
          return false;
        }
        $context = $stamp->getAdditionalArguments();
        return ($context['commentId'] ?? null) === $commentId;
      }));

    $controller = new UpdateCommentController();
    $controller->setCommandBus($commandBus);

    $command = new UpdateCommentCommand('Updated content');

    $controller($command, $user, $commentId);
  }

  #[Test]
  public function itPassesUserIdToCommand(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(JwtUser::class);
    $userId = 'owner-user-id';
    $commentId = 'comment-123';

    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn($userId);

    $commandBus->expects($this->once())
      ->method('handle')
      ->with($this->callback(function (Envelope $envelope) use ($userId) {
        $message = $envelope->getMessage();
        if (!$message instanceof UpdateCommentCommand) {
          return false;
        }
        $stamp = $envelope->last(HandlerArgumentsStamp::class);
        if (!$stamp instanceof HandlerArgumentsStamp) {
          return false;
        }
        $context = $stamp->getAdditionalArguments();
        return ($context['userId'] ?? null) === $userId;
      }));

    $controller = new UpdateCommentController();
    $controller->setCommandBus($commandBus);

    $command = new UpdateCommentCommand('Updated content');

    $controller($command, $user, $commentId);
  }

  #[Test]
  public function itReturnsEmptyResponse(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(JwtUser::class);

    $user->method('getIdentifier')->willReturn('user-123');
    $commandBus->method('handle');

    $controller = new UpdateCommentController();
    $controller->setCommandBus($commandBus);

    $command = new UpdateCommentCommand('New content');

    $response = $controller($command, $user, 'comment-456');

    $this->assertEquals(204, $response->getStatusCode());
  }
}
