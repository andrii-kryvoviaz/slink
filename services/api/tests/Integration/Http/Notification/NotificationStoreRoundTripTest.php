<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Notification;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Notification\Domain\Enum\NotificationType;
use Slink\Notification\Domain\Notification;
use Slink\Notification\Domain\Repository\NotificationStoreRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Tests\Integration\Http\Store\ViewBackedStoreRoundTripTestCase;

/**
 * @extends ViewBackedStoreRoundTripTestCase<Notification>
 */
#[Group('sc-1176')]
final class NotificationStoreRoundTripTest extends ViewBackedStoreRoundTripTestCase {
  private const string TIMESTAMP_FORMAT = 'Y-m-d H:i:s';
  private const string STORED_TIMESTAMP = '2021-02-03 04:05:06';
  private const string UNKNOWN_NOTIFICATION_ID = '11111111-2222-4333-8444-555555555555';

  private string $adminToken = '';
  private string $ownerId = '';
  private string $ownerToken = '';
  private string $actorId = '';
  private string $actorToken = '';
  private string $imageId = '';

  #[\Override]
  protected function createAggregate(): ID {
    $this->bootParties();
    $parentId = $this->postComment($this->ownerToken, $this->imageId, 'owner words');
    $this->postComment($this->actorToken, $this->imageId, 'reply words', $parentId);

    return $this->notificationOfType(NotificationType::COMMENT_REPLY);
  }

  #[\Override]
  protected function fetch(ID $id): Notification {
    /** @var NotificationStoreRepositoryInterface $store */
    $store = static::getContainer()->get(NotificationStoreRepositoryInterface::class);

    return $store->get($id);
  }

  #[\Override]
  protected function aggregateState(object $aggregate): array {
    return [
      'userId' => $aggregate->getUserId()->toString(),
      'type' => $aggregate->getType()->value,
      'referenceId' => $aggregate->getReferenceId()->toString(),
      'relatedCommentId' => $aggregate->getRelatedCommentId()?->toString(),
      'actorId' => $aggregate->getActorId()?->toString(),
      'createdAt' => $aggregate->getCreatedAt()->format(self::TIMESTAMP_FORMAT),
      'readAt' => $aggregate->getReadAt()?->format(self::TIMESTAMP_FORMAT),
    ];
  }

  #[\Override]
  protected function storedState(ID $id): array {
    $row = $this->readModel()->fetchAssociative('SELECT * FROM "notification" WHERE uuid = ?', [$id->toString()]);
    self::assertIsArray($row);

    return [
      'userId' => $row['user_id'],
      'type' => $row['type'],
      'referenceId' => $row['reference_id'],
      'relatedCommentId' => $row['related_comment_id'],
      'actorId' => $row['actor_id'],
      'createdAt' => $this->secondsOf($row['created_at']),
      'readAt' => $this->secondsOf($row['read_at']),
    ];
  }

  #[\Override]
  protected function rootTable(): string {
    return 'notification';
  }

  #[Test]
  public function creatingAndReadingANotificationChangesItsRowAndAppendsNothing(): void {
    $this->bootParties();
    $logBefore = $this->eventLogSize();

    $this->postComment($this->actorToken, $this->imageId, 'actor words');
    $notificationId = $this->notificationOfType(NotificationType::COMMENT);
    self::assertNull($this->storedState($notificationId)['readAt']);

    $this->assertAccepted($this->markRead($notificationId->toString(), $this->ownerToken));
    self::assertNotNull($this->storedState($notificationId)['readAt']);

    self::assertSame($logBefore, $this->eventLogSize());
  }

  #[Test]
  public function aBookmarkNotificationReloadsWithItsActorAndNoRelatedComment(): void {
    $this->bootParties();
    self::assertContains(
      $this->apiRequest('POST', \sprintf('/api/image/%s/bookmark', $this->imageId), $this->actorToken),
      [200, 201],
    );

    $notification = $this->reload($this->notificationOfType(NotificationType::ADDED_TO_BOOKMARKS));

    self::assertSame(NotificationType::ADDED_TO_BOOKMARKS, $notification->getType());
    self::assertSame($this->ownerId, $notification->getUserId()->toString());
    self::assertSame($this->imageId, $notification->getReferenceId()->toString());
    self::assertSame($this->actorId, $notification->getActorId()?->toString());
    self::assertNull($notification->getRelatedCommentId());
  }

  #[Test]
  public function aNotificationWhoseActorWasDetachedReloadsWithNoActor(): void {
    $notificationId = $this->createAggregate();
    self::assertSame($this->actorId, $this->storedState($notificationId)['actorId']);

    self::assertSame(204, $this->apiRequest('DELETE', '/api/user/' . $this->actorId, $this->adminToken));

    self::assertNull($this->storedState($notificationId)['actorId']);
    self::assertNull($this->reload($notificationId)->getActorId());
  }

  #[Test]
  public function aNotificationMarkedReadInBulkReloadsAsReadAtThatTime(): void {
    $notificationId = $this->createAggregate();
    $this->assertAccepted($this->markAllRead());
    $readAt = $this->storedState($notificationId)['readAt'];
    self::assertNotNull($readAt);

    $notification = $this->reload($notificationId);

    self::assertTrue($notification->isRead());
    self::assertSame($readAt, $notification->getReadAt()?->format(self::TIMESTAMP_FORMAT));
  }

  #[Test]
  public function markingANotificationAlreadyReadInBulkKeepsItsReadTime(): void {
    $notificationId = $this->createAggregate();
    $this->assertAccepted($this->markAllRead());
    $this->readModel()->executeStatement(
      'UPDATE "notification" SET read_at = ? WHERE uuid = ?',
      [self::STORED_TIMESTAMP, $notificationId->toString()],
    );

    $this->assertAccepted($this->markRead($notificationId->toString(), $this->ownerToken));

    self::assertSame(self::STORED_TIMESTAMP, $this->storedState($notificationId)['readAt']);
  }

  #[Test]
  public function aNotificationKeepsItsRelatedCommentAfterThatCommentIsDeleted(): void {
    $notificationId = $this->createAggregate();
    $relatedCommentId = $this->storedState($notificationId)['relatedCommentId'];
    self::assertIsString($relatedCommentId);

    self::assertContains(
      $this->apiRequest('DELETE', \sprintf('/api/comment/%s', $relatedCommentId), $this->actorToken),
      [200, 202, 204],
    );

    self::assertSame($this->storedState($notificationId), $this->aggregateState($this->reload($notificationId)));
  }

  #[Test]
  public function aNotificationReloadsItsActorAfterThatUserIsSoftDeleted(): void {
    $notificationId = $this->createAggregate();

    $this->softDelete($this->adminToken, $this->actorId);

    self::assertSame($this->storedState($notificationId), $this->aggregateState($this->reload($notificationId)));
  }

  #[Test]
  public function aNotificationPurgedWithItsImageIsNotFound(): void {
    $notificationId = $this->createAggregate();
    self::assertContains(
      $this->apiRequest('DELETE', \sprintf('/api/image/%s', $this->imageId), $this->ownerToken),
      [200, 204],
    );
    self::assertSame(0, $this->countRowsByColumn('notification', 'uuid', $notificationId->toString()));

    $this->expectException(NotFoundException::class);

    $this->reload($notificationId);
  }

  #[Test]
  public function aNotificationPurgedWithItsRecipientIsNotFound(): void {
    $notificationId = $this->createAggregate();
    self::assertSame(204, $this->apiRequest('DELETE', '/api/user/' . $this->ownerId, $this->adminToken));
    self::assertSame(0, $this->countRowsByColumn('notification', 'uuid', $notificationId->toString()));

    $this->expectException(NotFoundException::class);

    $this->reload($notificationId);
  }

  #[Test]
  public function loadingAnUnknownNotificationThrowsNotFound(): void {
    $this->expectException(NotFoundException::class);

    $this->reload(ID::fromString(self::UNKNOWN_NOTIFICATION_ID));
  }

  #[Test]
  public function markingAnUnknownNotificationReadAnswersNotFound(): void {
    $this->bootParties();

    self::assertSame(404, $this->markRead(self::UNKNOWN_NOTIFICATION_ID, $this->ownerToken));
  }

  private function bootParties(): void {
    $this->adminToken = $this->bootAdmin();
    $this->ownerId = $this->createUser('notification-store-owner@local.test', 'notificationowner', self::PASSWORD);
    $this->actorId = $this->createUser('notification-store-actor@local.test', 'notificationactor', self::PASSWORD);
    $this->ownerToken = $this->login('notificationowner', self::PASSWORD);
    $this->actorToken = $this->login('notificationactor', self::PASSWORD);
    $this->imageId = $this->uploadImage($this->ownerToken, true);
  }

  private function notificationOfType(NotificationType $type): ID {
    $id = $this->readModel()->fetchOne(
      'SELECT uuid FROM "notification" WHERE user_id = ? AND type = ?',
      [$this->ownerId, $type->value],
    );
    self::assertIsString($id);

    return ID::fromString($id);
  }

  private function markRead(string $notificationId, string $token): int {
    return $this->apiRequest('POST', \sprintf('/api/notifications/%s/read', $notificationId), $token);
  }

  private function markAllRead(): int {
    return $this->apiRequest('POST', '/api/notifications/mark-all-read', $this->ownerToken);
  }

  private function assertAccepted(int $status): void {
    self::assertContains($status, [200, 204], (string) $this->client->getResponse()->getContent());
  }
}
