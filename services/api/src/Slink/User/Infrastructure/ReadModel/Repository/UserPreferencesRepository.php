<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;
use Slink\User\Infrastructure\ReadModel\View\UserPreferencesView;

final class UserPreferencesRepository extends AbstractRepository {
  protected static function entityClass(): string {
    return UserPreferencesView::class;
  }

  /**
   * @throws NonUniqueResultException
   */
  public function findByUserId(string $userId): ?UserPreferencesView {
    $qb = $this->createQueryBuilder('prefs')
      ->where('prefs.userId = :userId')
      ->setParameter('userId', $userId);

    return $qb->getQuery()->getOneOrNullResult();
  }

  public function save(UserPreferencesView $preferences): void {
    $em = $this->getEntityManager();
    $em->persist($preferences);
    $em->flush();
  }
}
