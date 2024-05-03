<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;
use Slink\User\Domain\Repository\UserRoleRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\UserRoleView;

class UserRoleRepository extends AbstractRepository implements UserRoleRepositoryInterface {
  
  /**
   * @inheritDoc
   */
  static protected function entityClass(): string {
    return UserRoleView::class;
  }
  
  /**
   * @param string $role
   * @return bool
   * @throws NonUniqueResultException
   */
  public function exists(string $role): bool {
    $result = $this->_em->createQueryBuilder()
      ->from(UserRoleView::class, 'r')
      ->select('r.role')
      ->where('r.role = :role')
      ->setParameter('role', $role)
      ->getQuery()
      ->getOneOrNullResult();
    
    return $result['role'] ?? false;
  }
}