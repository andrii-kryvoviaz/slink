<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Ramsey\Uuid\UuidInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;
use Slink\User\Domain\Repository\CheckUserByEmailInterface;
use Slink\User\Domain\Repository\CheckUserByRefreshTokenInterface;
use Slink\User\Domain\Repository\UserRepositoryInterface;
use Slink\User\Domain\ValueObject\Auth\HashedRefreshToken;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Infrastructure\ReadModel\View\RefreshTokenView;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class UserRepository extends AbstractRepository implements
  CheckUserByEmailInterface,
  CheckUserByRefreshTokenInterface,
  UserRepositoryInterface
{
  static protected function entityClass(): string {
    return UserView::class;
  }
  
  /**
   * @throws NonUniqueResultException
   */
  public function existsEmail(Email $email): ?UuidInterface {
    $result = $this->_em
      ->createQueryBuilder()
      ->from(UserView::class, 'user')
      ->select('user.uuid')
      ->where('user.email = :email')
      ->setParameter('email', $email->toString())
      ->getQuery()
      ->getOneOrNullResult();
    
    return $result['uuid'] ?? null;
  }
  
  /**
   * @throws NonUniqueResultException
   */
  #[\Override]
  public function existsByRefreshToken(HashedRefreshToken $hashedRefreshToken): ?UuidInterface {
    $result = $this->_em
      ->createQueryBuilder()
      ->from(UserView::class, 'user')
      ->select('user.uuid')
      ->join(RefreshTokenView::class, 'rt', 'WITH', 'user.uuid = rt.userUuid')
      ->where('rt.token = :hashedRefreshToken')
      ->setParameter('hashedRefreshToken', $hashedRefreshToken->toString())
      ->getQuery()
      ->getOneOrNullResult();
    
    return $result['uuid'] ?? null;
  }
  
  /**
   * @param ID $id
   * @return UserView
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function one(ID $id): UserView {
    $qb = $this->_em
      ->createQueryBuilder()
      ->from(UserView::class, 'user')
      ->select('user')
      ->where('user.uuid = :uuid')
      ->setParameter('uuid', $id->toString());
    
    return $this->oneOrException($qb);
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
      ->select('user')
      ->where('user.email = :email')
      ->setParameter('email', $email->toString());
    
    return $this->oneOrException($qb);
  }
  
  /**
   * @param UserView $userView
   * @return void
   */
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
}