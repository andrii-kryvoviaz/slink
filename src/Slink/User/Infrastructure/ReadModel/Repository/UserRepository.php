<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Ramsey\Uuid\UuidInterface;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;
use Slink\User\Domain\Repository\CheckUserByEmailInterface;
use Slink\User\Domain\Repository\GetUserCredentialsByEmailInterface;
use Slink\User\Domain\Repository\UserRepositoryInterface;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class UserRepository extends AbstractRepository implements CheckUserByEmailInterface, GetUserCredentialsByEmailInterface, UserRepositoryInterface {
  
  /**
   * @throws NonUniqueResultException
   */
  public function existsEmail(Email $email): ?UuidInterface {
    $userId = $this->_em
      ->createQueryBuilder()
      ->from(UserView::class, 'user')
      ->select('user.uuid')
      ->where('user.email = :email')
      ->setParameter('email', $email->toString())
      ->getQuery()
      ->getOneOrNullResult();
    
    return $userId['uuid'] ?? null;
  }
  
  /**
   * @param Email $email
   * @return UserView
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function oneByEmail(Email $email): UserView {
    $qb = $this->_em
      ->createQueryBuilder()
      ->from(UserView::class, 'user')
      ->select('
        user.uuid,
        user.email,
        user.createdAt,
        user.updatedAt'
      )
      ->where('user.email = :email')
      ->setParameter('email', $email->toString());
    
    return $this->oneOrException($qb);
  }

  public function add(UserView $userView): void {
    $this->_em->persist($userView);
  }
  
  /**
   * @param Email $email
   * @return array<int, mixed>
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function getCredentialsByEmail(Email $email): array {
    $qb = $this->_em
      ->createQueryBuilder()
      ->from(UserView::class, 'user')
      ->select('
        user.uuid,
        user.email,
        user.password'
      )
      ->where('user.email = :email')
      ->setParameter('email', $email->toString());
    
    $user = $this->oneOrException($qb);
    
    return [
      $user['uuid'],
      $user['email'],
      $user['password'],
    ];
  }
  
  static protected function entityClass(): string {
    return UserView::class;
  }
}