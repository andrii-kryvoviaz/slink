<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Notification\Domain;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Comment\Infrastructure\ReadModel\View\CommentView;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Notification\Domain\Enum\NotificationType;
use Slink\Notification\Domain\Event\NotificationWasRead;
use Slink\Notification\Domain\Notification;
use Slink\Notification\Infrastructure\ReadModel\View\NotificationView;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class NotificationTest extends TestCase {
  #[Test]
  public function itCreatesCommentNotification(): void {
    $id = ID::generate();
    $userId = ID::generate();
    $referenceId = ID::generate();

    $notification = Notification::create(
      $id,
      $userId,
      NotificationType::COMMENT,
      $referenceId,
    );

    $this->assertEquals($userId, $notification->getUserId());
    $this->assertEquals(NotificationType::COMMENT, $notification->getType());
    $this->assertEquals($referenceId, $notification->getReferenceId());
    $this->assertFalse($notification->isRead());
    $this->assertNull($notification->getReadAt());
  }

  #[Test]
  public function itCreatesCommentReplyNotification(): void {
    $id = ID::generate();
    $userId = ID::generate();
    $referenceId = ID::generate();
    $relatedCommentId = ID::generate();
    $actorId = ID::generate();

    $notification = Notification::create(
      $id,
      $userId,
      NotificationType::COMMENT_REPLY,
      $referenceId,
      $relatedCommentId,
      $actorId,
    );

    $this->assertEquals(NotificationType::COMMENT_REPLY, $notification->getType());
    $this->assertEquals($relatedCommentId, $notification->getRelatedCommentId());
    $this->assertEquals($actorId, $notification->getActorId());
  }

  #[Test]
  public function itCreatesFavoriteNotification(): void {
    $id = ID::generate();
    $userId = ID::generate();
    $referenceId = ID::generate();

    $notification = Notification::create(
      $id,
      $userId,
      NotificationType::ADDED_TO_BOOKMARKS,
      $referenceId,
    );

    $this->assertEquals(NotificationType::ADDED_TO_BOOKMARKS, $notification->getType());
  }

  #[Test]
  public function itMarksNotificationAsRead(): void {
    $notification = $this->createNotification();

    $this->assertFalse($notification->isRead());

    $notification->markAsRead();

    $this->assertTrue($notification->isRead());
    $this->assertNotNull($notification->getReadAt());
  }

  #[Test]
  public function itDoesNotMarkAlreadyReadNotification(): void {
    $notification = $this->createNotification();
    $notification->markAsRead();
    $firstReadAt = $notification->getReadAt();

    $notification->markAsRead();

    $this->assertEquals($firstReadAt, $notification->getReadAt());
  }

  #[Test]
  public function itHasCreatedAtTimestamp(): void {
    $notification = $this->createNotification();

    $this->assertInstanceOf(DateTime::class, $notification->getCreatedAt());
  }

  #[Test]
  public function itCreatesNotificationWithoutOptionalFields(): void {
    $notification = Notification::create(
      ID::generate(),
      ID::generate(),
      NotificationType::COMMENT,
      ID::generate(),
    );

    $this->assertNull($notification->getRelatedCommentId());
    $this->assertNull($notification->getActorId());
  }

  #[Test]
  public function itCreatesNotificationWithAllFields(): void {
    $id = ID::generate();
    $userId = ID::generate();
    $referenceId = ID::generate();
    $relatedCommentId = ID::generate();
    $actorId = ID::generate();

    $notification = Notification::create(
      $id,
      $userId,
      NotificationType::COMMENT_REPLY,
      $referenceId,
      $relatedCommentId,
      $actorId,
    );

    $this->assertEquals($userId, $notification->getUserId());
    $this->assertEquals(NotificationType::COMMENT_REPLY, $notification->getType());
    $this->assertEquals($referenceId, $notification->getReferenceId());
    $this->assertEquals($relatedCommentId, $notification->getRelatedCommentId());
    $this->assertEquals($actorId, $notification->getActorId());
  }

  #[Test]
  #[Group('sc-1176')]
  public function itHydratesEveryFieldFromItsView(): void {
    $id = ID::generate();
    $recipientId = ID::generate();
    $imageId = ID::generate();
    $actorId = ID::generate();
    $commentId = ID::generate();
    $createdAt = DateTime::fromString('2021-02-03 04:05:06');
    $readAt = DateTime::fromString('2022-03-04 05:06:07');

    $notification = Notification::fromView(new NotificationView(
      $id->toString(),
      $this->createUserView($recipientId),
      NotificationType::COMMENT_REPLY,
      $this->createImageView($imageId),
      $this->createCommentView($commentId),
      $this->createUserView($actorId),
      $createdAt,
      true,
      $readAt,
    ));

    $this->assertTrue($id->equals($notification->aggregateRootId()));
    $this->assertSame($recipientId->toString(), $notification->getUserId()->toString());
    $this->assertSame(NotificationType::COMMENT_REPLY, $notification->getType());
    $this->assertSame($imageId->toString(), $notification->getReferenceId()->toString());
    $this->assertSame($commentId->toString(), $notification->getRelatedCommentId()?->toString());
    $this->assertSame($actorId->toString(), $notification->getActorId()?->toString());
    $this->assertTrue($createdAt->equals($notification->getCreatedAt()));
    $this->assertTrue($readAt->equals($notification->getReadAt()));
    $this->assertTrue($notification->isRead());
  }

  #[Test]
  #[Group('sc-1176')]
  public function itHydratesWithoutActorOrRelatedComment(): void {
    $notification = Notification::fromView($this->createUnreadView());

    $this->assertNull($notification->getActorId());
    $this->assertNull($notification->getRelatedCommentId());
    $this->assertNull($notification->getReadAt());
    $this->assertFalse($notification->isRead());
  }

  #[Test]
  #[Group('sc-1176')]
  public function itRecordsNoEventsWhenHydrated(): void {
    $this->assertSame([], Notification::fromView($this->createUnreadView())->releaseEvents());
  }

  #[Test]
  #[Group('sc-1176')]
  public function itMarksAHydratedUnreadNotificationAsRead(): void {
    $notification = Notification::fromView($this->createUnreadView());

    $notification->markAsRead();

    $this->assertTrue($notification->isRead());
    $events = $notification->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(NotificationWasRead::class, $events[0]);
  }

  #[Test]
  #[Group('sc-1176')]
  public function itDoesNotMarkAHydratedReadNotificationAgain(): void {
    $readAt = DateTime::fromString('2022-03-04 05:06:07');
    $notification = Notification::fromView(new NotificationView(
      ID::generate()->toString(),
      $this->createUserView(ID::generate()),
      NotificationType::COMMENT,
      $this->createImageView(ID::generate()),
      null,
      null,
      DateTime::now(),
      true,
      $readAt,
    ));

    $notification->markAsRead();

    $this->assertSame([], $notification->releaseEvents());
    $this->assertTrue($readAt->equals($notification->getReadAt()));
  }

  private function createUnreadView(): NotificationView {
    return new NotificationView(
      ID::generate()->toString(),
      $this->createUserView(ID::generate()),
      NotificationType::ADDED_TO_BOOKMARKS,
      $this->createImageView(ID::generate()),
      null,
      null,
      DateTime::now(),
    );
  }

  private function createUserView(ID $id): UserView {
    $user = $this->createStub(UserView::class);
    $user->method('getUuid')->willReturn($id->toString());

    return $user;
  }

  private function createImageView(ID $id): ImageView {
    $image = $this->createStub(ImageView::class);
    $image->method('getUuid')->willReturn($id->toString());

    return $image;
  }

  private function createCommentView(ID $id): CommentView {
    return new CommentView(
      $id->toString(),
      $this->createImageView(ID::generate()),
      $this->createUserView(ID::generate()),
      'related words',
      DateTime::now(),
    );
  }

  private function createNotification(): Notification {
    return Notification::create(
      ID::generate(),
      ID::generate(),
      NotificationType::COMMENT,
      ID::generate(),
    );
  }
}
