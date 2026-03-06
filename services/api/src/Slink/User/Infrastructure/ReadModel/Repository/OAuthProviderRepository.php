<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Slink\Shared\Domain\Enum\SortDirection;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;
use Slink\User\Domain\Enum\OAuthProvider;
use Slink\User\Domain\Filter\OAuthProviderFilter;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;

class OAuthProviderRepository extends AbstractRepository implements OAuthProviderRepositoryInterface {
  static protected function entityClass(): string {
    return OAuthProviderView::class;
  }

  /**
   * @throws NonUniqueResultException
   */
  public function findByProvider(OAuthProvider $provider): ?OAuthProviderView {
    return $this->getEntityManager()
      ->createQueryBuilder()
      ->from(OAuthProviderView::class, 'p')
      ->select('p')
      ->where('p.slug = :slug')
      ->setParameter('slug', $provider->value)
      ->getQuery()
      ->getOneOrNullResult();
  }

  /**
   * @return array<int, OAuthProviderView>
   */
  public function getProviders(OAuthProviderFilter $filter): array {
    $qb = $this->createQueryBuilder('p');

    if ($filter->isEnabledOnly()) {
      $qb->andWhere('p.enabled = true');
    }

    $qb->orderBy('p.sortOrder', 'ASC')
      ->addOrderBy('p.id', 'ASC');

    return $qb->getQuery()->getResult();
  }

  /**
   * @throws NonUniqueResultException
   */
  public function findById(ID $id): ?OAuthProviderView {
    return $this->getEntityManager()
      ->createQueryBuilder()
      ->from(OAuthProviderView::class, 'p')
      ->select('p')
      ->where('p.id = :id')
      ->setParameter('id', $id->toString())
      ->getQuery()
      ->getOneOrNullResult();
  }

  public function save(OAuthProviderView $provider): void {
    $this->getEntityManager()->persist($provider);
  }

  public function delete(OAuthProviderView $provider): void {
    $this->getEntityManager()->remove($provider);
  }

  public function findNeighbor(float $sortOrder, SortDirection $direction): ?OAuthProviderView {
    $qb = $this->createQueryBuilder('p');

    if ($direction === SortDirection::Up) {
      $qb->where('p.sortOrder < :sortOrder')
        ->orderBy('p.sortOrder', 'DESC')
        ->addOrderBy('p.id', 'DESC');
    } else {
      $qb->where('p.sortOrder > :sortOrder')
        ->orderBy('p.sortOrder', 'ASC')
        ->addOrderBy('p.id', 'ASC');
    }

    $qb->setParameter('sortOrder', $sortOrder)
      ->setMaxResults(1);

    return $qb->getQuery()->getOneOrNullResult();
  }

  public function getMaxSortOrder(): float {
    $result = $this->createQueryBuilder('p')
      ->select('MAX(p.sortOrder)')
      ->getQuery()
      ->getSingleScalarResult();

    return (float)($result ?? 0);
  }
}
