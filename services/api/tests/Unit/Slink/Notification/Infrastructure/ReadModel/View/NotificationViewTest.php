<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Notification\Infrastructure\ReadModel\View;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Comment\Infrastructure\ReadModel\View\CommentView;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Notification\Domain\Enum\NotificationType;
use Slink\Notification\Infrastructure\ReadModel\View\NotificationView;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Username;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class NotificationViewTest extends TestCase {
  private function createUser(): UserView {
    return new UserView(
      ID::generate()->toString(),
      Email::fromString('actor@example.com'),
      Username::fromString('actor'),
      DisplayName::fromString('Actor'),
      HashedPassword::encode('password123'),
      DateTime::now(),
      null,
      UserStatus::Active,
      null,
    );
  }

  private function createComment(string $content, ?DateTime $deletedAt = null): CommentView {
    return new CommentView(
      ID::generate()->toString(),
      $this->createStub(ImageView::class),
      $this->createUser(),
      $content,
      DateTime::now(),
      null,
      $deletedAt,
    );
  }

  private function createNotification(?UserView $actor, ?CommentView $relatedComment = null): NotificationView {
    return new NotificationView(
      ID::generate()->toString(),
      $this->createUser(),
      NotificationType::COMMENT,
      $this->createStub(ImageView::class),
      $relatedComment,
      $actor,
      DateTime::now(),
    );
  }

  #[Test]
  public function actorSummaryExposesOnlyIdAndDisplayName(): void {
    $notification = $this->createNotification($this->createUser());

    $summary = $notification->getActorSummary();

    self::assertNotNull($summary);
    self::assertSame(['id', 'displayName'], \array_keys($summary));
  }

  #[Test]
  public function actorSummaryIsNullWithoutActor(): void {
    $notification = $this->createNotification(null);

    self::assertNull($notification->getActorSummary());
  }

  #[Test]
  public function relatedCommentSummaryExposesLiveParent(): void {
    $parent = $this->createComment('parent words');
    $reply = $this->createComment('reply words');
    $reply->setReferencedComment($parent);

    $summary = $this->createNotification(null, $reply)->getRelatedCommentSummary();

    self::assertNotNull($summary);
    self::assertSame(['id', 'content', 'isDeleted', 'referencedComment'], \array_keys($summary));
    self::assertSame(['id' => $parent->getId(), 'content' => 'parent words'], $summary['referencedComment']);
  }

  #[Test]
  public function relatedCommentSummaryHasNoParentWithoutOne(): void {
    $summary = $this->createNotification(null, $this->createComment('plain words'))->getRelatedCommentSummary();

    self::assertNotNull($summary);
    self::assertArrayHasKey('referencedComment', $summary);
    self::assertNull($summary['referencedComment']);
  }

  #[Test]
  public function relatedCommentSummaryHidesDeletedParent(): void {
    $reply = $this->createComment('reply words');
    $reply->setReferencedComment($this->createComment('parent words', DateTime::now()));

    $summary = $this->createNotification(null, $reply)->getRelatedCommentSummary();

    self::assertNotNull($summary);
    self::assertNull($summary['referencedComment']);
  }

  #[Test]
  public function relatedCommentSummaryIsNullWithoutRelatedComment(): void {
    self::assertNull($this->createNotification(null)->getRelatedCommentSummary());
  }
}
