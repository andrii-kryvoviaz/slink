<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Comment;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Comment\Application\Command\CreateComment\CreateCommentCommand;
use Slink\Comment\Domain\Repository\CommentRepositoryInterface;
use Slink\Comment\Infrastructure\ReadModel\View\CommentView;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Infrastructure\Auth\JwtUser;
use Slink\User\Infrastructure\ReadModel\View\UserView;
use Symfony\Component\Messenger\Envelope;
use UI\Http\Rest\Controller\Comment\CreateCommentController;
use UI\Http\Rest\Response\ApiResponse;

final class CreateCommentControllerTest extends TestCase {

  private function createController(
    CommandBusInterface $commandBus,
    CommentRepositoryInterface $commentRepository,
  ): CreateCommentController {
    $controller = new CreateCommentController($commentRepository);
    $controller->setCommandBus($commandBus);
    return $controller;
  }

  private function createMockCommentView(): CommentView {
    $userView = $this->createMock(UserView::class);
    $userView->method('getUuid')->willReturn('user-123');
    $userView->method('getDisplayName')->willReturn('Test User');
    
    $mock = $this->createMock(CommentView::class);
    $mock->method('getId')->willReturn('comment-123');
    $mock->method('getContent')->willReturn('Test content');
    $mock->method('getDisplayContent')->willReturn('Test content');
    $mock->method('getCreatedAt')->willReturn(DateTime::now());
    $mock->method('getUpdatedAt')->willReturn(null);
    $mock->method('getDeletedAt')->willReturn(null);
    $mock->method('isDeleted')->willReturn(false);
    $mock->method('isEdited')->willReturn(false);
    $mock->method('getUser')->willReturn($userView);
    $mock->method('getReferencedComment')->willReturn(null);
    $mock->method('getReferencedCommentSummary')->willReturn(null);
    
    return $mock;
  }

  #[Test]
  public function itCreatesCommentSuccessfully(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $commentRepository = $this->createMock(CommentRepositoryInterface::class);
    $user = $this->createMock(JwtUser::class);
    $imageId = 'image-123';
    $commentId = ID::generate();
    $commentView = $this->createMockCommentView();

    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn('user-456');

    $commandBus->expects($this->once())
      ->method('handleSync')
      ->with($this->callback(function ($envelope) {
        return $envelope instanceof Envelope
          && $envelope->getMessage() instanceof CreateCommentCommand;
      }))
      ->willReturn($commentId);

    $commentRepository->expects($this->once())
      ->method('oneById')
      ->with($commentId->toString())
      ->willReturn($commentView);

    $controller = $this->createController($commandBus, $commentRepository);
    $command = new CreateCommentCommand('This is a test comment');

    $response = $controller($command, $user, $imageId);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(201, $response->getStatusCode());
  }

  #[Test]
  public function itCreatesReplyCommentSuccessfully(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $commentRepository = $this->createMock(CommentRepositoryInterface::class);
    $user = $this->createMock(JwtUser::class);
    $imageId = 'image-123';
    $referencedCommentId = 'comment-789';
    $commentId = ID::generate();
    $commentView = $this->createMockCommentView();

    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn('user-456');

    $commandBus->expects($this->once())
      ->method('handleSync')
      ->willReturn($commentId);

    $commentRepository->expects($this->once())
      ->method('oneById')
      ->with($commentId->toString())
      ->willReturn($commentView);

    $controller = $this->createController($commandBus, $commentRepository);
    $command = new CreateCommentCommand('This is a reply', $referencedCommentId);

    $response = $controller($command, $user, $imageId);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(201, $response->getStatusCode());
  }

  #[Test]
  public function itPassesImageIdToCommand(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $commentRepository = $this->createMock(CommentRepositoryInterface::class);
    $user = $this->createMock(JwtUser::class);
    $imageId = 'specific-image-id';
    $commentId = ID::generate();
    $commentView = $this->createMockCommentView();

    $user->method('getIdentifier')->willReturn('user-123');

    $commandBus->expects($this->once())
      ->method('handleSync')
      ->with($this->callback(function (Envelope $envelope) {
        $message = $envelope->getMessage();
        return $message instanceof CreateCommentCommand;
      }))
      ->willReturn($commentId);

    $commentRepository->method('oneById')->willReturn($commentView);

    $controller = $this->createController($commandBus, $commentRepository);
    $command = new CreateCommentCommand('Test comment');

    $controller($command, $user, $imageId);
  }

  #[Test]
  public function itPassesUserIdToCommand(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $commentRepository = $this->createMock(CommentRepositoryInterface::class);
    $user = $this->createMock(JwtUser::class);
    $userId = 'specific-user-id';
    $imageId = 'image-123';
    $commentId = ID::generate();
    $commentView = $this->createMockCommentView();

    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn($userId);

    $commandBus->expects($this->once())
      ->method('handleSync')
      ->with($this->callback(function (Envelope $envelope) {
        $message = $envelope->getMessage();
        return $message instanceof CreateCommentCommand;
      }))
      ->willReturn($commentId);

    $commentRepository->method('oneById')->willReturn($commentView);

    $controller = $this->createController($commandBus, $commentRepository);
    $command = new CreateCommentCommand('Test comment');

    $controller($command, $user, $imageId);
  }
}
