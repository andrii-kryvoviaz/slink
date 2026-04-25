<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\ReadModel\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Slink\Collection\Domain\Filter\CollectionListFilter;
use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionView;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Pagination\CursorPaginationTrait;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;

final class CollectionRepository extends AbstractRepository implements CollectionRepositoryInterface {
  use CursorPaginationTrait;

  static protected function entityClass(): string {
    return CollectionView::class;
  }

  public function add(CollectionView $collection): void {
    $this->getEntityManager()->persist($collection);
  }

  public function remove(CollectionView $collection): void {
    $this->getEntityManager()->remove($collection);
  }

  public function oneById(string $id): CollectionView {
    $collection = $this->findById($id);

    if ($collection === null) {
      throw new NotFoundException('Collection not found');
    }

    return $collection;
  }

  public function findById(string $id): ?CollectionView {
    $qb = $this->getEntityManager()
      ->createQueryBuilder()
      ->from(CollectionView::class, 'c')
      ->select('c')
      ->where('c.uuid = :id')
      ->setParameter('id', $id);

    try {
      return $qb->getQuery()->getOneOrNullResult();
    } catch (NonUniqueResultException) {
      return null;
    }
  }

  /**
   * @return Paginator<CollectionView>
   */
  public function getByUserId(CollectionListFilter $filter): Paginator {
    $qb = $this->buildCollectionListQuery($filter)
      ->orderBy('c.createdAt', 'DESC')
      ->addOrderBy('c.uuid', 'DESC')
      ->setMaxResults(($filter->getLimit() ?? 12) + 1);

    if ($cursor = $filter->getCursor()) {
      $this->applyCursorPagination($qb, $cursor, 'createdAt', 'desc', 'uuid', 'c');
    }

    return new Paginator($qb->getQuery());
  }

  public function countByUserId(CollectionListFilter $filter): int {
    $qb = $this->buildCollectionListQuery($filter)
      ->select('COUNT(c.uuid)');

    return (int) $qb->getQuery()->getSingleScalarResult();
  }

  public function existsByFilter(CollectionListFilter $filter): bool {
    $qb = $this->buildCollectionListQuery($filter)
      ->select('1')
      ->setMaxResults(1);

    return (bool) $qb->getQuery()->getOneOrNullResult();
  }

  private function buildCollectionListQuery(CollectionListFilter $filter): QueryBuilder {
    $qb = $this->getEntityManager()
      ->createQueryBuilder()
      ->from(CollectionView::class, 'c')
      ->select('c')
      ->where('c.user = :userId')
      ->setParameter('userId', $filter->getUserId());

    if ($searchTerm = $filter->getSearchTerm()) {
      $qb->andWhere('c.name LIKE :searchTerm OR c.description LIKE :searchTerm')
        ->setParameter('searchTerm', "%{$searchTerm}%");
    }

    return $qb;
  }

  public function findNamesByPatternAndUser(string $baseName, string $userId): array {
    $qb = $this->getEntityManager()
      ->createQueryBuilder()
      ->from(CollectionView::class, 'c')
      ->select('c.name')
      ->where('c.user = :userId')
      ->andWhere('c.name = :exactName OR c.name LIKE :pattern')
      ->setParameter('userId', $userId)
      ->setParameter('exactName', $baseName)
      ->setParameter('pattern', $baseName . ' (%)');

    return array_column($qb->getQuery()->getArrayResult(), 'name');
  }

  public function findByIds(array $ids): array {
    if ($ids === []) {
      return [];
    }

    return $this->createQueryBuilder('c')
      ->where('c.uuid IN (:ids)')
      ->setParameter('ids', $ids)
      ->getQuery()
      ->getResult();
  }
}
