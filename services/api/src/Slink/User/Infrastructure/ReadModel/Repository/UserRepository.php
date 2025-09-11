<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Ramsey\Uuid\UuidInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Filter\UserListFilter;
use Slink\User\Domain\Repository\CheckUserByDisplayNameInterface;
use Slink\User\Domain\Repository\CheckUserByEmailInterface;
use Slink\User\Domain\Repository\CheckUserByRefreshTokenInterface;
use Slink\User\Domain\Repository\CheckUserByUsernameInterface;
use Slink\User\Domain\Repository\UserRepositoryInterface;
use Slink\User\Domain\ValueObject\Auth\HashedRefreshToken;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Username;
use Slink\User\Infrastructure\ReadModel\View\RefreshTokenView;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class UserRepository extends AbstractRepository implements
  CheckUserByEmailInterface,
  CheckUserByUsernameInterface,
  CheckUserByDisplayNameInterface,
  CheckUserByRefreshTokenInterface,
  UserRepositoryInterface
{
  static protected function entityClass(): string {
    return UserView::class;
  }
  
  /**
   * @throws NonUniqueResultException
   */
  #[\Override]
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
  public function existsUsername(Username $username): ?UuidInterface {
    $result = $this->_em
      ->createQueryBuilder()
      ->from(UserView::class, 'user')
      ->select('user.uuid')
      ->where('user.username = :username')
      ->setParameter('username', $username->toString())
      ->getQuery()
      ->getOneOrNullResult();
    
    return $result['uuid'] ?? null;
  }
  
  /**
   * @throws NonUniqueResultException
   */
  #[\Override]
  public function existsDisplayName(DisplayName $displayName): ?UuidInterface {
    $result = $this->_em
      ->createQueryBuilder()
      ->from(UserView::class, 'user')
      ->select('user.uuid')
      ->where('user.displayName = :displayName')
      ->setParameter('displayName', $displayName->toString())
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
   * @param Username $username
   * @return UserView
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function oneByUsername(Username $username): UserView {
    $qb = $this->_em
      ->createQueryBuilder()
      ->from(UserView::class, 'user')
      ->select('user')
      ->where('user.username = :username')
      ->setParameter('username', $username->toString());
    
    return $this->oneOrException($qb);
  }
  
  /**
   * @param int $page
   * @param UserListFilter $filter
   * @return Paginator<UserView>
   */
  public function getUserList(int $page, UserListFilter $filter): Paginator {
    $qb = $this->_em
      ->createQueryBuilder()
      ->from(UserView::class, 'user')
      ->select('user')
      ->where('user.status != :status')
      ->setParameter('status', UserStatus::Deleted)
      ->orderBy('user.' . $filter->getOrderBy(), $filter->getOrder())
      ->setFirstResult(($page - 1) * $filter->getLimit())
      ->setMaxResults($filter->getLimit());
    
    if ($filter->getSearch()) {
      $qb
        ->andWhere('user.email LIKE :search OR user.displayName LIKE :search')
        ->setParameter('search', $filter->getSearch() . '%');
    }
    
    return new Paginator($qb);
  }
  
  /**
   * @param UserView $userView
   * @return void
   */
  public function save(UserView $userView): void {
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
