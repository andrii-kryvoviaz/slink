<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\Repository;

use Slink\Collection\Infrastructure\ReadModel\View\CollectionItemView;

interface CollectionItemRepositoryInterface {
  public function add(CollectionItemView $item): void;

  public function remove(CollectionItemView $item): void;

  public function findByCollectionAndItemId(string $collectionId, string $itemId): ?CollectionItemView;

  /**
   * @return array<CollectionItemView>
   */
  public function findAllByItemId(string $itemId): array;

  /**
   * @return array<CollectionItemView>
   */
  public function getByCollectionId(string $collectionId): array;

  /**
   * @return array<CollectionItemView>
   */
  public function getByCollectionIdSorted(string $collectionId): array;

  /**
   * @return array<CollectionItemView>
   */
  public function getByCollectionIdPaginated(string $collectionId, int $page, int $limit): array;

  public function countByCollectionId(string $collectionId): int;

  /**
   * @param string[] $collectionIds
   * @return array<string, int>
   */
  public function countByCollectionIds(array $collectionIds): array;

  /**
   * @param string[] $imageIds
   * @return array<string, string[]> Map of imageId => collectionIds[]
   */
  public function getCollectionIdsByImageIds(array $imageIds): array;

  /**
   * @param string[] $collectionIds
   * @param int $limit
   * @return array<string, string[]> Map of collectionId => imageIds[]
   */
  public function getFirstImageIdsByCollectionIds(array $collectionIds, int $limit = 5): array;
}
