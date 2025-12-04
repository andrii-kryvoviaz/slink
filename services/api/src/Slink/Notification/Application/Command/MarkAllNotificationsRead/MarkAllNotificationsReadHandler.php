<?php

declare(strict_types=1);

namespace Slink\Notification\Application\Command\MarkAllNotificationsRead;

use Slink\Notification\Domain\Repository\NotificationRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;

final readonly class MarkAllNotificationsReadHandler implements CommandHandlerInterface {
  public function __construct(
    private NotificationRepositoryInterface $notificationRepository,
  ) {
  }

  public function __invoke(MarkAllNotificationsReadCommand $command, string $userId): void {
    $unreadCount = $this->notificationRepository->countUnreadByUserId($userId);

    if ($unreadCount === 0) {
      return;
    }

    $this->notificationRepository->markAllAsReadByUserId($userId);
  }
}
