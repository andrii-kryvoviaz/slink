<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Notification\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Notification\Domain\Enum\NotificationType;
use Slink\Notification\Domain\Notification;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

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

  private function createNotification(): Notification {
    return Notification::create(
      ID::generate(),
      ID::generate(),
      NotificationType::COMMENT,
      ID::generate(),
    );
  }
}
