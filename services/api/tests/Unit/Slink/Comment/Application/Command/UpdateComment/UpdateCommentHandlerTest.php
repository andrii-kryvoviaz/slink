<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Comment\Application\Command\UpdateComment;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Comment\Application\Command\UpdateComment\UpdateCommentCommand;
use Slink\Comment\Application\Command\UpdateComment\UpdateCommentHandler;
use Slink\Comment\Domain\Comment;
use Slink\Comment\Domain\Repository\CommentStoreRepositoryInterface;
use Slink\Comment\Domain\ValueObject\CommentContent;
use Slink\Shared\Domain\Exception\ForbiddenException;
use Slink\Shared\Domain\ValueObject\ID;

final class UpdateCommentHandlerTest extends TestCase {
  #[Test]
  public function itUpdatesCommentSuccessfully(): void {
    $commentId = ID::generate();
    $userId = ID::generate();
    $commentStore = $this->createMock(CommentStoreRepositoryInterface::class);

    $comment = Comment::create(
      $commentId,
      ID::generate(),
      $userId,
      null,
      CommentContent::fromString('Original content'),
    );

    $commentStore->expects($this->once())
      ->method('get')
      ->with($this->callback(fn (ID $id) => $id->equals($commentId)))
      ->willReturn($comment);

    $commentStore->expects($this->once())
      ->method('store')
      ->with($comment);

    $handler = new UpdateCommentHandler($commentStore);

    $command = new UpdateCommentCommand('Updated content');

    $handler($command, $commentId->toString(), $userId->toString());

    $this->assertEquals('Updated content', $comment->getContent()->toString());
  }

  #[Test]
  public function itThrowsForbiddenExceptionWhenUserIsNotOwner(): void {
    $this->expectException(ForbiddenException::class);
    $this->expectExceptionMessage('You can only edit your own comments');

    $commentId = ID::generate();
    $ownerId = ID::generate();
    $differentUserId = ID::generate();
    $commentStore = $this->createMock(CommentStoreRepositoryInterface::class);

    $comment = Comment::create(
      $commentId,
      ID::generate(),
      $ownerId,
      null,
      CommentContent::fromString('Original content'),
    );

    $commentStore->expects($this->once())
      ->method('get')
      ->willReturn($comment);

    $handler = new UpdateCommentHandler($commentStore);

    $command = new UpdateCommentCommand('Attempted update');

    $handler($command, $commentId->toString(), $differentUserId->toString());
  }

  #[Test]
  public function itStoresUpdatedComment(): void {
    $commentId = ID::generate();
    $userId = ID::generate();
    $commentStore = $this->createMock(CommentStoreRepositoryInterface::class);

    $comment = Comment::create(
      $commentId,
      ID::generate(),
      $userId,
      null,
      CommentContent::fromString('Original content'),
    );

    $commentStore->method('get')->willReturn($comment);

    $commentStore->expects($this->once())
      ->method('store')
      ->with($this->callback(function (Comment $storedComment) {
        return $storedComment->getContent()->toString() === 'New content';
      }));

    $handler = new UpdateCommentHandler($commentStore);

    $command = new UpdateCommentCommand('New content');

    $handler($command, $commentId->toString(), $userId->toString());
  }

  #[Test]
  public function itSetsUpdatedAtTimestamp(): void {
    $commentId = ID::generate();
    $userId = ID::generate();
    $commentStore = $this->createStub(CommentStoreRepositoryInterface::class);

    $comment = Comment::create(
      $commentId,
      ID::generate(),
      $userId,
      null,
      CommentContent::fromString('Original content'),
    );

    $this->assertNull($comment->getUpdatedAt());

    $commentStore->method('get')->willReturn($comment);
    $commentStore->method('store');

    $handler = new UpdateCommentHandler($commentStore);

    $command = new UpdateCommentCommand('Updated content');

    $handler($command, $commentId->toString(), $userId->toString());

    $this->assertNotNull($comment->getUpdatedAt());
  }
}
