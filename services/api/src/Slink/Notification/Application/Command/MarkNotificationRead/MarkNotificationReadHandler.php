<?php

declare(strict_types=1);

namespace Slink\Notification\Application\Command\MarkNotificationRead;

use Slink\Notification\Domain\Repository\NotificationRepositoryInterface;
use Slink\Notification\Domain\Repository\NotificationStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\Exception\ForbiddenException;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class MarkNotificationReadHandler implements CommandHandlerInterface {
  public function __construct(
    private NotificationStoreRepositoryInterface $notificationStore,
    private NotificationRepositoryInterface $notificationRepository,
  ) {
  }

  public function __invoke(MarkNotificationReadCommand $command, string $notificationId, string $userId): void {
    $notificationView = $this->notificationRepository->oneById($notificationId);

    if ($notificationView->getUserId() !== $userId) {
      throw new ForbiddenException('You can only mark your own notifications as read');
    }

    $notification = $this->notificationStore->get(ID::fromString($notificationId));
    $notification->markAsRead();

    $this->notificationStore->store($notification);
  }
}
