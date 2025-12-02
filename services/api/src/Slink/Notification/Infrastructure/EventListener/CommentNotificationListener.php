<?php

declare(strict_types=1);

namespace Slink\Notification\Infrastructure\EventListener;

use Slink\Comment\Domain\Event\CommentWasCreated;
use Slink\Comment\Domain\Repository\CommentRepositoryInterface;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Notification\Domain\Enum\NotificationType;
use Slink\Notification\Domain\Notification;
use Slink\Notification\Domain\Repository\NotificationStoreRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;

final class CommentNotificationListener extends AbstractProjection {
  public function __construct(
    private readonly NotificationStoreRepositoryInterface $notificationStore,
    private readonly ImageRepositoryInterface $imageRepository,
    private readonly CommentRepositoryInterface $commentRepository,
  ) {
  }

  public function handleCommentWasCreated(CommentWasCreated $event): void {
    $image = $this->imageRepository->oneById($event->imageId->toString());
    $imageOwner = $image->getUser();

    if ($imageOwner === null) {
      return;
    }

    $commentAuthorId = $event->userId->toString();
    $imageOwnerId = $imageOwner->getUuid();
    $referencedCommentAuthorId = null;

    if ($event->referencedCommentId !== null) {
      $referencedCommentAuthorId = $this->notifyReferencedCommentAuthor($event, $commentAuthorId);
    }

    if ($commentAuthorId !== $imageOwnerId && $imageOwnerId !== $referencedCommentAuthorId) {
      $this->notifyImageOwner($event, $imageOwnerId);
    }
  }

  private function notifyImageOwner(CommentWasCreated $event, string $imageOwnerId): void {
    $notification = Notification::create(
      ID::generate(),
      ID::fromString($imageOwnerId),
      NotificationType::COMMENT,
      $event->imageId,
      $event->id,
      $event->userId,
    );

    $this->notificationStore->store($notification);
  }

  private function notifyReferencedCommentAuthor(CommentWasCreated $event, string $commentAuthorId): ?string {
    if ($event->referencedCommentId === null) {
      return null;
    }

    try {
      $referencedComment = $this->commentRepository->oneById($event->referencedCommentId->toString());
      $referencedAuthorId = $referencedComment->getUserId();

      if ($referencedAuthorId === $commentAuthorId) {
        return null;
      }

      $notification = Notification::create(
        ID::generate(),
        ID::fromString($referencedAuthorId),
        NotificationType::COMMENT_REPLY,
        $event->imageId,
        $event->id,
        $event->userId,
      );

      $this->notificationStore->store($notification);

      return $referencedAuthorId;
    } catch (\Exception) {
      return null;
    }
  }
}
