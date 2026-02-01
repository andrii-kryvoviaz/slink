<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\ReadModel\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionItemView;
use Slink\Shared\Infrastructure\Pagination\CursorPaginationTrait;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;

final class CollectionItemRepository extends AbstractRepository implements CollectionItemRepositoryInterface {
  use CursorPaginationTrait;
  static protected function entityClass(): string {
    return CollectionItemView::class;
  }

  public function getCollectionItemsByCursor(
    string $collectionId,
    int $limit,
    ?string $cursor = null
  ): Paginator {
    $qb = $this->getEntityManager()
      ->createQueryBuilder()
      ->from(CollectionItemView::class, 'ci')
      ->select('ci')
      ->join('ci.collection', 'c')
      ->where('c.uuid = :collectionId')
      ->setParameter('collectionId', $collectionId)
      ->orderBy('ci.addedAt', 'DESC')
      ->addOrderBy('ci.uuid', 'DESC')
      ->setMaxResults($limit + 1);

    if ($cursor) {
      $this->applyCursorPagination($qb, $cursor, 'addedAt', 'desc', 'uuid', 'ci');
    }

    return new Paginator($qb->getQuery(), false);
  }

  public function countCollectionItems(string $collectionId): int {
    return (int) $this->getEntityManager()
      ->createQueryBuilder()
      ->from(CollectionItemView::class, 'ci')
      ->select('COUNT(ci.uuid)')
      ->join('ci.collection', 'c')
      ->where('c.uuid = :collectionId')
      ->setParameter('collectionId', $collectionId)
      ->getQuery()
      ->getSingleScalarResult();
  }

  public function add(CollectionItemView $item): void {
    $this->getEntityManager()->persist($item);
  }

  public function remove(CollectionItemView $item): void {
    $this->getEntityManager()->remove($item);
  }

  public function findByCollectionAndItemId(string $collectionId, string $itemId): ?CollectionItemView {
    return $this->getEntityManager()
      ->createQueryBuilder()
      ->from(CollectionItemView::class, 'ci')
      ->select('ci')
      ->join('ci.collection', 'c')
      ->where('c.uuid = :collectionId')
      ->andWhere('ci.itemId = :itemId')
      ->setParameter('collectionId', $collectionId)
      ->setParameter('itemId', $itemId)
      ->getQuery()
      ->getOneOrNullResult();
  }

  public function findAllByItemId(string $itemId): array {
    return $this->getEntityManager()
      ->createQueryBuilder()
      ->from(CollectionItemView::class, 'ci')
      ->select('ci')
      ->where('ci.itemId = :itemId')
      ->setParameter('itemId', $itemId)
      ->getQuery()
      ->getResult();
  }

  /**
   * @return array<CollectionItemView>
   */
  public function getByCollectionId(string $collectionId): array {
    return $this->getEntityManager()
      ->createQueryBuilder()
      ->from(CollectionItemView::class, 'ci')
      ->select('ci')
      ->join('ci.collection', 'c')
      ->where('c.uuid = :collectionId')
      ->setParameter('collectionId', $collectionId)
      ->getQuery()
      ->getResult();
  }

  /**
   * @return array<CollectionItemView>
   */
  public function getByCollectionIdSorted(string $collectionId): array {
    return $this->getEntityManager()
      ->createQueryBuilder()
      ->from(CollectionItemView::class, 'ci')
      ->select('ci')
      ->join('ci.collection', 'c')
      ->where('c.uuid = :collectionId')
      ->setParameter('collectionId', $collectionId)
      ->orderBy('ci.position', 'ASC')
      ->getQuery()
      ->getResult();
  }

  /**
   * @return array<CollectionItemView>
   */
  public function getByCollectionIdPaginated(string $collectionId, int $page, int $limit): array {
    $offset = ($page - 1) * $limit;

    return $this->getEntityManager()
      ->createQueryBuilder()
      ->from(CollectionItemView::class, 'ci')
      ->select('ci')
      ->join('ci.collection', 'c')
      ->where('c.uuid = :collectionId')
      ->setParameter('collectionId', $collectionId)
      ->orderBy('ci.position', 'ASC')
      ->setFirstResult($offset)
      ->setMaxResults($limit)
      ->getQuery()
      ->getResult();
  }

  public function countByCollectionId(string $collectionId): int {
    return (int) $this->getEntityManager()
      ->createQueryBuilder()
      ->from(CollectionItemView::class, 'ci')
      ->select('COUNT(ci.uuid)')
      ->join('ci.collection', 'c')
      ->where('c.uuid = :collectionId')
      ->setParameter('collectionId', $collectionId)
      ->getQuery()
      ->getSingleScalarResult();
  }

  public function countByCollectionIds(array $collectionIds): array {
    if (empty($collectionIds)) {
      return [];
    }

    $results = $this->getEntityManager()
      ->createQueryBuilder()
      ->from(CollectionItemView::class, 'ci')
      ->select('c.uuid as collectionId, COUNT(ci.uuid) as itemCount')
      ->join('ci.collection', 'c')
      ->where('c.uuid IN (:collectionIds)')
      ->setParameter('collectionIds', $collectionIds)
      ->groupBy('c.uuid')
      ->getQuery()
      ->getResult();

    $counts = [];
    foreach ($results as $row) {
      $counts[(string) $row['collectionId']] = (int) $row['itemCount'];
    }

    return $counts;
  }

  public function getCollectionIdsByImageIds(array $imageIds): array {
    if (empty($imageIds)) {
      return [];
    }

    $results = $this->getEntityManager()
      ->createQueryBuilder()
      ->from(CollectionItemView::class, 'ci')
      ->select('ci.itemId, c.uuid as collectionId')
      ->join('ci.collection', 'c')
      ->where('ci.itemId IN (:imageIds)')
      ->setParameter('imageIds', $imageIds)
      ->getQuery()
      ->getResult();

    $map = [];
    foreach ($results as $row) {
      $imageId = (string) $row['itemId'];
      $map[$imageId][] = (string) $row['collectionId'];
    }

    return $map;
  }

  public function getFirstImageIdsByCollectionIds(array $collectionIds, int $limit = 5): array {
    if (empty($collectionIds)) {
      return [];
    }

    $results = $this->getEntityManager()
      ->createQueryBuilder()
      ->from(CollectionItemView::class, 'ci')
      ->select('c.uuid as collectionId, ci.itemId, ci.position')
      ->join('ci.collection', 'c')
      ->where('c.uuid IN (:collectionIds)')
      ->andWhere('ci.itemType = :imageType')
      ->setParameter('collectionIds', $collectionIds)
      ->setParameter('imageType', 'image')
      ->orderBy('c.uuid', 'ASC')
      ->addOrderBy('ci.position', 'ASC')
      ->getQuery()
      ->getResult();

    $grouped = [];
    foreach ($results as $row) {
      $collectionId = (string) $row['collectionId'];
      if (!isset($grouped[$collectionId])) {
        $grouped[$collectionId] = [];
      }
      if (count($grouped[$collectionId]) < $limit) {
        $grouped[$collectionId][] = (string) $row['itemId'];
      }
    }

    return $grouped;
  }
}
