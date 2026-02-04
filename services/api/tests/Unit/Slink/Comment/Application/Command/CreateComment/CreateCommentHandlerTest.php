<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Comment\Application\Command\CreateComment;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Comment\Application\Command\CreateComment\CreateCommentCommand;
use Slink\Comment\Application\Command\CreateComment\CreateCommentHandler;
use Slink\Comment\Domain\Comment;
use Slink\Comment\Domain\Repository\CommentStoreRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;

final class CreateCommentHandlerTest extends TestCase {
  #[Test]
  public function itCreatesCommentSuccessfully(): void {
    $commentStore = $this->createMock(CommentStoreRepositoryInterface::class);

    $commentStore->expects($this->once())
      ->method('store')
      ->with($this->isInstanceOf(Comment::class));

    $handler = new CreateCommentHandler($commentStore);

    $command = new CreateCommentCommand('This is a test comment');
    $imageId = 'image-123';
    $userId = 'user-456';

    $result = $handler($command, $imageId, $userId);

    $this->assertInstanceOf(ID::class, $result);
  }

  #[Test]
  public function itCreatesReplyCommentSuccessfully(): void {
    $commentStore = $this->createMock(CommentStoreRepositoryInterface::class);

    $commentStore->expects($this->once())
      ->method('store')
      ->with($this->callback(function (Comment $comment) {
        return $comment->getReferencedCommentId() !== null;
      }));

    $handler = new CreateCommentHandler($commentStore);

    $referencedCommentId = ID::generate()->toString();
    $command = new CreateCommentCommand('This is a reply', $referencedCommentId);
    $imageId = 'image-123';
    $userId = 'user-456';

    $result = $handler($command, $imageId, $userId);

    $this->assertInstanceOf(ID::class, $result);
  }

  #[Test]
  public function itStoresCommentWithCorrectImageId(): void {
    $commentStore = $this->createMock(CommentStoreRepositoryInterface::class);
    $imageId = 'specific-image-id';

    $commentStore->expects($this->once())
      ->method('store')
      ->with($this->callback(function (Comment $comment) use ($imageId) {
        return $comment->getImageId()->toString() === $imageId;
      }));

    $handler = new CreateCommentHandler($commentStore);

    $command = new CreateCommentCommand('Test comment');

    $handler($command, $imageId, 'user-123');
  }

  #[Test]
  public function itStoresCommentWithCorrectUserId(): void {
    $commentStore = $this->createMock(CommentStoreRepositoryInterface::class);
    $userId = 'specific-user-id';

    $commentStore->expects($this->once())
      ->method('store')
      ->with($this->callback(function (Comment $comment) use ($userId) {
        return $comment->getUserId()->toString() === $userId;
      }));

    $handler = new CreateCommentHandler($commentStore);

    $command = new CreateCommentCommand('Test comment');

    $handler($command, 'image-123', $userId);
  }

  #[Test]
  public function itStoresCommentWithCorrectContent(): void {
    $commentStore = $this->createMock(CommentStoreRepositoryInterface::class);
    $content = 'This is the exact comment content';

    $commentStore->expects($this->once())
      ->method('store')
      ->with($this->callback(function (Comment $comment) use ($content) {
        return $comment->getContent()->toString() === $content;
      }));

    $handler = new CreateCommentHandler($commentStore);

    $command = new CreateCommentCommand($content);

    $handler($command, 'image-123', 'user-456');
  }

  #[Test]
  public function itCreatesCommentWithoutReferencedComment(): void {
    $commentStore = $this->createMock(CommentStoreRepositoryInterface::class);

    $commentStore->expects($this->once())
      ->method('store')
      ->with($this->callback(function (Comment $comment) {
        return $comment->getReferencedCommentId() === null;
      }));

    $handler = new CreateCommentHandler($commentStore);

    $command = new CreateCommentCommand('Top-level comment');

    $handler($command, 'image-123', 'user-456');
  }

  #[Test]
  public function itReturnsGeneratedCommentId(): void {
    $commentStore = $this->createStub(CommentStoreRepositoryInterface::class);

    $handler = new CreateCommentHandler($commentStore);

    $command = new CreateCommentCommand('Test comment');

    $result1 = $handler($command, 'image-123', 'user-456');
    $result2 = $handler($command, 'image-123', 'user-456');

    $this->assertNotEquals($result1->toString(), $result2->toString());
  }
}
