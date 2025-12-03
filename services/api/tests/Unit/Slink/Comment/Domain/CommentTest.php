<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Comment\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Comment\Domain\Comment;
use Slink\Comment\Domain\Exception\CommentEditWindowExpiredException;
use Slink\Comment\Domain\ValueObject\CommentContent;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final class CommentTest extends TestCase {
  #[Test]
  public function itCreatesComment(): void {
    $id = ID::generate();
    $imageId = ID::generate();
    $userId = ID::generate();
    $content = CommentContent::fromString('Test comment content');

    $comment = Comment::create($id, $imageId, $userId, null, $content);

    $this->assertEquals($imageId, $comment->getImageId());
    $this->assertEquals($userId, $comment->getUserId());
    $this->assertEquals($content, $comment->getContent());
    $this->assertNull($comment->getReferencedCommentId());
    $this->assertFalse($comment->isDeleted());
    $this->assertNull($comment->getUpdatedAt());
    $this->assertNull($comment->getDeletedAt());
  }

  #[Test]
  public function itCreatesReplyComment(): void {
    $id = ID::generate();
    $imageId = ID::generate();
    $userId = ID::generate();
    $referencedCommentId = ID::generate();
    $content = CommentContent::fromString('Reply comment');

    $comment = Comment::create($id, $imageId, $userId, $referencedCommentId, $content);

    $this->assertEquals($referencedCommentId, $comment->getReferencedCommentId());
  }

  #[Test]
  public function itEditsComment(): void {
    $comment = $this->createComment();
    $newContent = CommentContent::fromString('Updated content');

    $comment->edit($newContent);

    $this->assertEquals('Updated content', $comment->getContent()->toString());
    $this->assertNotNull($comment->getUpdatedAt());
  }

  #[Test]
  public function itDeletesComment(): void {
    $comment = $this->createComment();

    $comment->delete();

    $this->assertTrue($comment->isDeleted());
    $this->assertNotNull($comment->getDeletedAt());
  }

  #[Test]
  public function itDoesNotDeleteAlreadyDeletedComment(): void {
    $comment = $this->createComment();
    $comment->delete();
    $firstDeletedAt = $comment->getDeletedAt();

    $comment->delete();

    $this->assertEquals($firstDeletedAt, $comment->getDeletedAt());
  }

  #[Test]
  public function itCannotEditDeletedComment(): void {
    $this->expectException(\DomainException::class);
    $this->expectExceptionMessage('Cannot edit a deleted comment');

    $comment = $this->createComment();
    $comment->delete();

    $comment->edit(CommentContent::fromString('Should fail'));
  }

  #[Test]
  public function itChecksOwnership(): void {
    $userId = ID::generate();
    $differentUserId = ID::generate();

    $comment = Comment::create(
      ID::generate(),
      ID::generate(),
      $userId,
      null,
      CommentContent::fromString('Test'),
    );

    $this->assertTrue($comment->isOwnedBy($userId));
    $this->assertFalse($comment->isOwnedBy($differentUserId));
  }

  #[Test]
  public function itHasCreatedAtTimestamp(): void {
    $comment = $this->createComment();

    $this->assertInstanceOf(DateTime::class, $comment->getCreatedAt());
  }

  #[Test]
  public function itUpdatesContentOnEdit(): void {
    $comment = $this->createComment();
    $originalContent = $comment->getContent()->toString();

    $comment->edit(CommentContent::fromString('New content'));

    $this->assertNotEquals($originalContent, $comment->getContent()->toString());
    $this->assertEquals('New content', $comment->getContent()->toString());
  }

  private function createComment(): Comment {
    return Comment::create(
      ID::generate(),
      ID::generate(),
      ID::generate(),
      null,
      CommentContent::fromString('Original content'),
    );
  }
}
