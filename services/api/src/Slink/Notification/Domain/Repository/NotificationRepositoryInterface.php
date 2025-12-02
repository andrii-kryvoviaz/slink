<?php

declare(strict_types=1);

namespace Slink\Notification\Domain\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Slink\Notification\Infrastructure\ReadModel\View\NotificationView;

interface NotificationRepositoryInterface extends ServiceEntityRepositoryInterface {
  public function add(NotificationView $notification): void;

  public function oneById(string $id): NotificationView;

  public function findByUserId(string $userId, int $page = 1, int $limit = 20): Paginator;

  public function countUnreadByUserId(string $userId): int;

  public function markAllAsReadByUserId(string $userId): void;
}
