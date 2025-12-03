<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Comment;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Comment\Application\Command\DeleteComment\DeleteCommentCommand;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\HandlerArgumentsStamp;
use UI\Http\Rest\Controller\Comment\DeleteCommentController;
use UI\Http\Rest\Response\ApiResponse;

final class DeleteCommentControllerTest extends TestCase {
  #[Test]
  public function itDeletesCommentSuccessfully(): void {
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
          && $envelope->getMessage() instanceof DeleteCommentCommand;
      }));

    $controller = new DeleteCommentController();
    $controller->setCommandBus($commandBus);

    $response = $controller($user, $commentId);

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
        if (!$message instanceof DeleteCommentCommand) {
          return false;
        }
        $stamp = $envelope->last(HandlerArgumentsStamp::class);
        if (!$stamp instanceof HandlerArgumentsStamp) {
          return false;
        }
        $context = $stamp->getAdditionalArguments();
        return ($context['commentId'] ?? null) === $commentId;
      }));

    $controller = new DeleteCommentController();
    $controller->setCommandBus($commandBus);

    $controller($user, $commentId);
  }

  #[Test]
  public function itPassesUserIdToCommand(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(JwtUser::class);
    $userId = 'deleting-user-id';
    $commentId = 'comment-123';

    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn($userId);

    $commandBus->expects($this->once())
      ->method('handle')
      ->with($this->callback(function (Envelope $envelope) use ($userId) {
        $message = $envelope->getMessage();
        if (!$message instanceof DeleteCommentCommand) {
          return false;
        }
        $stamp = $envelope->last(HandlerArgumentsStamp::class);
        if (!$stamp instanceof HandlerArgumentsStamp) {
          return false;
        }
        $context = $stamp->getAdditionalArguments();
        return ($context['userId'] ?? null) === $userId;
      }));

    $controller = new DeleteCommentController();
    $controller->setCommandBus($commandBus);

    $controller($user, $commentId);
  }

  #[Test]
  public function itReturnsEmptyResponse(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(JwtUser::class);

    $user->method('getIdentifier')->willReturn('user-123');
    $commandBus->method('handle');

    $controller = new DeleteCommentController();
    $controller->setCommandBus($commandBus);

    $response = $controller($user, 'comment-456');

    $this->assertEquals(204, $response->getStatusCode());
  }
}
