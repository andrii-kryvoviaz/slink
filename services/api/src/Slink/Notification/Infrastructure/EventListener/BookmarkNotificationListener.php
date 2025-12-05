<?php

declare(strict_types=1);

namespace Slink\Notification\Infrastructure\EventListener;

use Slink\Bookmark\Domain\Event\BookmarkWasCreated;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Notification\Domain\Enum\NotificationType;
use Slink\Notification\Domain\Notification;
use Slink\Notification\Domain\Repository\NotificationStoreRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;

final class BookmarkNotificationListener extends AbstractProjection {
  public function __construct(
    private readonly NotificationStoreRepositoryInterface $notificationStore,
    private readonly ImageRepositoryInterface $imageRepository,
  ) {
  }

  public function handleBookmarkWasCreated(BookmarkWasCreated $event): void {
    $image = $this->imageRepository->oneById($event->imageId->toString());
    $imageOwner = $image->getUser();

    if ($imageOwner === null) {
      return;
    }

    $bookmarkUserId = $event->userId->toString();
    $imageOwnerId = $imageOwner->getUuid();

    if ($bookmarkUserId === $imageOwnerId) {
      return;
    }

    $notification = Notification::create(
      ID::generate(),
      ID::fromString($imageOwnerId),
      NotificationType::ADDED_TO_BOOKMARKS,
      $event->imageId,
      null,
      $event->userId,
    );

    $this->notificationStore->store($notification);
  }
}
