<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Repository;

use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\EventStore\AbstractStoreRepository;
use Slink\User\Domain\Repository\CheckUserByEmailInterface;
use Slink\User\Domain\Repository\CheckUserByRefreshTokenInterface;
use Slink\User\Domain\Repository\CheckUserByUsernameInterface;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Auth\HashedRefreshToken;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Username;
use Symfony\Contracts\Service\Attribute\Required;

final class UserStore extends AbstractStoreRepository implements UserStoreRepositoryInterface {
  private CheckUserByEmailInterface $checkUserByEmail;
  private CheckUserByUsernameInterface $checkUserByUsername;
  private CheckUserByRefreshTokenInterface $getUserByRefreshToken;
  
  #[Required]
  public function setCheckUserByEmail(CheckUserByEmailInterface $checkUserByEmail): void {
    $this->checkUserByEmail = $checkUserByEmail;
  }
  
  #[Required]
  public function setCheckUserByUsername(CheckUserByUsernameInterface $checkUserByUsername): void {
    $this->checkUserByUsername = $checkUserByUsername;
  }
  
  #[Required]
  public function setGetUserByRefreshToken(CheckUserByRefreshTokenInterface $getUserByRefreshToken): void {
    $this->getUserByRefreshToken = $getUserByRefreshToken;
  }
  
  static function getAggregateRootClass(): string {
    return User::class;
  }
  
  /**
   * @param ID $id
   * @return User
   */
  #[\Override]
  public function get(ID $id): User {
    $user = $this->retrieve($id);
    if (!$user instanceof User) {
      throw new \RuntimeException('Expected instance of User, got ' . get_class($user));
    }
    return $user;
  }
  
  /**
   * @param Email|Username $username
   * @return User|null
   */
  #[\Override]
  public function getByUsername(Email|Username $username): ?User {
    $id = $username instanceof Email
      ? $this->checkUserByEmail->existsEmail($username)
      : $this->checkUserByUsername->existsUsername($username);
    
    if ($id === null) {
      return null;
    }
    
    return $this->get(ID::fromString($id->toString()));
  }
  
  /**
   * @param HashedRefreshToken $hashedRefreshToken
   * @return User|null
   */
  #[\Override]
  public function getByRefreshToken(HashedRefreshToken $hashedRefreshToken): ?User {
    $id = $this->getUserByRefreshToken->existsByRefreshToken($hashedRefreshToken);
    if ($id === null) {
      return null;
    }
    
    return $this->get(ID::fromString($id->toString()));
  }
  
  /**
   * @param User $user
   * @return void
   */
  #[\Override]
  public function store(User $user): void {
    $user->refreshToken->clearDanglingRefreshTokens();
    
    $this->persist($user);
  }
}