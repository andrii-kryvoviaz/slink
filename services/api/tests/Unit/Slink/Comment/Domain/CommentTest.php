<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Comment\Domain;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Comment\Domain\Comment;
use Slink\Comment\Domain\Event\CommentWasDeleted;
use Slink\Comment\Domain\Event\CommentWasEdited;
use Slink\Comment\Domain\Exception\CommentEditWindowExpiredException;
use Slink\Comment\Domain\ValueObject\CommentContent;
use Slink\Comment\Infrastructure\ReadModel\View\CommentView;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Username;
use Slink\User\Infrastructure\ReadModel\View\UserView;

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

  #[Test]
  #[Group('sc-1176')]
  public function itHydratesEveryFieldFromItsView(): void {
    $id = ID::generate();
    $imageId = ID::generate();
    $author = $this->createAuthor(UserStatus::Active);
    $createdAt = DateTime::fromString('2021-02-03 04:05:06');
    $updatedAt = DateTime::fromString('2022-03-04 05:06:07');
    $view = new CommentView(
      $id->toString(),
      $this->createImage($imageId),
      $author,
      'stored words',
      $createdAt,
      $updatedAt,
    );
    $parent = $this->createView('parent words');
    $view->setReferencedComment($parent);

    $comment = Comment::fromView($view);

    $this->assertTrue($id->equals($comment->aggregateRootId()));
    $this->assertSame($imageId->toString(), $comment->getImageId()->toString());
    $this->assertSame($author->getUuid(), $comment->getUserId()->toString());
    $this->assertSame($parent->getId(), $comment->getReferencedCommentId()?->toString());
    $this->assertSame('stored words', $comment->getContent()->toString());
    $this->assertTrue($createdAt->equals($comment->getCreatedAt()));
    $this->assertTrue($updatedAt->equals($comment->getUpdatedAt()));
    $this->assertNull($comment->getDeletedAt());
    $this->assertFalse($comment->isDeleted());
  }

  #[Test]
  #[Group('sc-1176')]
  public function itRecordsNoEventsWhenHydrated(): void {
    $this->assertSame([], Comment::fromView($this->createView('stored words'))->releaseEvents());
  }

  #[Test]
  #[Group('sc-1176')]
  public function itTakesStoredContentWithoutEscapingItAgain(): void {
    $stored = 'Tom &amp; &quot;Jerry&quot; &lt;b&gt;';

    $comment = Comment::fromView($this->createView($stored));

    $this->assertSame($stored, $comment->getContent()->toString());
  }

  #[Test]
  #[Group('sc-1176')]
  public function itTakesStoredContentLongerThanTheInputLimit(): void {
    $stored = \str_repeat('&amp;', 2000);

    $comment = Comment::fromView($this->createView($stored));

    $this->assertSame($stored, $comment->getContent()->toString());
  }

  #[Test]
  #[Group('sc-1176')]
  public function itStillEscapesEditsOfAHydratedComment(): void {
    $comment = Comment::fromView($this->createView('Tom &amp; Jerry'));

    $comment->edit(CommentContent::fromString('Tom & Spike'));

    $this->assertSame('Tom &amp; Spike', $comment->getContent()->toString());
    $events = $comment->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(CommentWasEdited::class, $events[0]);
    $this->assertSame('Tom &amp; Spike', $events[0]->content->toString());
  }

  #[Test]
  #[Group('sc-1176')]
  public function itKeepsTheAuthorIdOfADeletedUser(): void {
    $author = $this->createAuthor(UserStatus::Deleted);

    $comment = Comment::fromView($this->createView('stored words', $author));

    $this->assertSame($author->getUuid(), $comment->getUserId()->toString());
    $this->assertTrue($comment->isOwnedBy(ID::fromString($author->getUuid())));
  }

  #[Test]
  #[Group('sc-1176')]
  public function aHydratedSoftDeletedCommentRefusesEditAndDelete(): void {
    $deletedAt = DateTime::fromString('2021-02-03 04:05:06');
    $comment = Comment::fromView($this->createView('stored words', null, $deletedAt));

    $this->assertTrue($comment->isDeleted());
    $this->assertTrue($deletedAt->equals($comment->getDeletedAt()));

    $comment->delete();

    $this->assertSame([], $comment->releaseEvents());
    $this->assertTrue($deletedAt->equals($comment->getDeletedAt()));

    $this->expectException(\DomainException::class);
    $this->expectExceptionMessage('Cannot edit a deleted comment');

    $comment->edit(CommentContent::fromString('Should fail'));
  }

  #[Test]
  #[Group('sc-1176')]
  public function itDeletesAHydratedComment(): void {
    $comment = Comment::fromView($this->createView('stored words'));

    $comment->delete();

    $this->assertTrue($comment->isDeleted());
    $events = $comment->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(CommentWasDeleted::class, $events[0]);
  }

  private function createView(string $content, ?UserView $author = null, ?DateTime $deletedAt = null): CommentView {
    return new CommentView(
      ID::generate()->toString(),
      $this->createImage(ID::generate()),
      $author ?? $this->createAuthor(UserStatus::Active),
      $content,
      DateTime::now(),
      null,
      $deletedAt,
    );
  }

  private function createImage(ID $imageId): ImageView {
    $image = $this->createStub(ImageView::class);
    $image->method('getUuid')->willReturn($imageId->toString());

    return $image;
  }

  private function createAuthor(UserStatus $status): UserView {
    return new UserView(
      ID::generate()->toString(),
      Email::fromString('author@example.com'),
      Username::fromString('author'),
      DisplayName::fromString('Author'),
      HashedPassword::encode('password123'),
      DateTime::now(),
      null,
      $status,
      null,
    );
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
