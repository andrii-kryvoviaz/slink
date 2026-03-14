<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionView;

interface CollectionRepositoryInterface {
  public function add(CollectionView $collection): void;

  public function remove(CollectionView $collection): void;

  public function oneById(string $id): CollectionView;

  public function findById(string $id): ?CollectionView;

  public function getByUserId(string $userId, int $limit, ?string $cursor = null): Paginator;

  public function countByUserId(string $userId): int;

  /**
   * @return string[]
   */
  public function findNamesByPatternAndUser(string $baseName, string $userId): array;
}
