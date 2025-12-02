<?php

declare(strict_types=1);

namespace Slink\Notification\Application\Query\GetUnreadCount;

use Slink\Notification\Domain\Repository\NotificationRepositoryInterface;
use Slink\Shared\Application\Query\QueryHandlerInterface;

final readonly class GetUnreadCountHandler implements QueryHandlerInterface {
  public function __construct(
    private NotificationRepositoryInterface $notificationRepository,
  ) {
  }

  public function __invoke(GetUnreadCountQuery $query, string $userId): array {
    $count = $this->notificationRepository->countUnreadByUserId($userId);

    return [
      'count' => $count,
    ];
  }
}
