<?php

declare(strict_types=1);

namespace Slink\Notification\Application\Query\GetNotificationsExists;

use Slink\Notification\Domain\Repository\NotificationRepositoryInterface;
use Slink\Shared\Application\Query\QueryHandlerInterface;

final readonly class GetNotificationsExistsHandler implements QueryHandlerInterface {
  public function __construct(
    private NotificationRepositoryInterface $repository,
  ) {
  }

  public function __invoke(GetNotificationsExistsQuery $query, string $userId): bool {
    return $this->repository->existsByUserId($userId);
  }
}
