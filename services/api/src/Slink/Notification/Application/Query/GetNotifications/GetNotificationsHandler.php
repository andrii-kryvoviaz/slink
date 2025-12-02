<?php

declare(strict_types=1);

namespace Slink\Notification\Application\Query\GetNotifications;

use Slink\Notification\Domain\Repository\NotificationRepositoryInterface;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;

final readonly class GetNotificationsHandler implements QueryHandlerInterface {
  public function __construct(
    private NotificationRepositoryInterface $notificationRepository,
  ) {
  }

  public function __invoke(GetNotificationsQuery $query, string $userId): Collection {
    $paginator = $this->notificationRepository->findByUserId(
      $userId,
      $query->getPage(),
      $query->getLimit(),
    );

    $notifications = iterator_to_array($paginator);
    $total = $paginator->count();

    $items = array_map(fn($notification) => Item::fromEntity($notification), $notifications);

    return new Collection(
      $query->getPage(),
      $query->getLimit(),
      $total,
      $items,
    );
  }
}
