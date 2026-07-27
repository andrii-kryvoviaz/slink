<?php

declare(strict_types=1);

namespace Slink\Notification\Infrastructure\EventConsumer;

use Slink\Image\Domain\Event\ImageWasDeleted;
use Slink\Notification\Domain\Repository\NotificationRepositoryInterface;
use Slink\Shared\Infrastructure\MessageBus\Event\AbstractEventConsumer;
use Slink\User\Domain\Event\UserWasPurged;

final class NotificationPurger extends AbstractEventConsumer {
  public function __construct(
    private readonly NotificationRepositoryInterface $notificationRepository,
  ) {
  }

  public function handleUserWasPurged(UserWasPurged $event): void {
    $userId = $event->id->toString();

    $this->notificationRepository->deleteByUserId($userId);
    $this->notificationRepository->detachActor($userId);
  }

  public function handleImageWasDeleted(ImageWasDeleted $event): void {
    $this->notificationRepository->deleteByImageId($event->id->toString());
  }
}
