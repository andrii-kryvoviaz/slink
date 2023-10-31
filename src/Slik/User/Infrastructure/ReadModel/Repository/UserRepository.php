<?php

declare(strict_types=1);

namespace Slik\User\Infrastructure\ReadModel\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Ramsey\Uuid\UuidInterface;
use Slik\Shared\Infrastructure\Exception\NotFoundException;
use Slik\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;
use Slik\User\Domain\Repository\CheckUserByEmailInterface;
use Slik\User\Domain\Repository\GetUserCredentialsByEmailInterface;
use Slik\User\Domain\ValueObject\Email;
use Slik\User\Infrastructure\ReadModel\View\UserView;

final class UserRepository extends AbstractRepository implements CheckUserByEmailInterface, GetUserCredentialsByEmailInterface {
  
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
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function oneByEmail(Email $email) {
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

  static function entityClass(): string {
    return UserView::class;
  }
  
  /**
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
}