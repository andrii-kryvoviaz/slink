<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\ReadModel\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

  public function getByUserId(string $userId, int $limit, ?string $cursor = null): Paginator {
    $qb = $this->getEntityManager()
      ->createQueryBuilder()
      ->from(CollectionView::class, 'c')
      ->select('c')
      ->where('c.user = :userId')
      ->setParameter('userId', $userId)
      ->orderBy('c.createdAt', 'DESC')
      ->addOrderBy('c.uuid', 'DESC')
      ->setMaxResults($limit + 1);

    if ($cursor) {
      $this->applyCursorPagination($qb, $cursor, 'createdAt', 'desc', 'uuid', 'c');
    }

    return new Paginator($qb->getQuery());
  }

  public function countByUserId(string $userId): int {
    $qb = $this->getEntityManager()
      ->createQueryBuilder()
      ->from(CollectionView::class, 'c')
      ->select('COUNT(c.uuid)')
      ->where('c.user = :userId')
      ->setParameter('userId', $userId);

    return (int) $qb->getQuery()->getSingleScalarResult();
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
}
