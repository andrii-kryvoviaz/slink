<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\ReadModel\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionView;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;

final class CollectionRepository extends AbstractRepository implements CollectionRepositoryInterface {
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

  public function getByUserId(string $userId, int $page, int $limit): Paginator {
    $qb = $this->getEntityManager()
      ->createQueryBuilder()
      ->from(CollectionView::class, 'c')
      ->select('c')
      ->where('c.user = :userId')
      ->setParameter('userId', $userId)
      ->orderBy('c.createdAt', 'DESC')
      ->setFirstResult(($page - 1) * $limit)
      ->setMaxResults($limit);

    return new Paginator($qb->getQuery());
  }
}
