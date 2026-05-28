<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Slink\Collection\Domain\Filter\CollectionListFilter;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionView;

interface CollectionRepositoryInterface {
  public function add(CollectionView $collection): void;

  public function remove(CollectionView $collection): void;

  public function oneById(string $id): CollectionView;

  public function findById(string $id): ?CollectionView;

  /**
   * @return Paginator<CollectionView>
   */
  public function getByUserId(CollectionListFilter $filter): Paginator;

  public function countByUserId(CollectionListFilter $filter): int;

  public function existsByFilter(CollectionListFilter $filter): bool;

  /**
   * @return string[]
   */
  public function findNamesByPatternAndUser(string $baseName, string $userId): array;

  /**
   * @param list<string> $ids
   * @return CollectionView[]
   */
  public function findByIds(array $ids): array;
}
