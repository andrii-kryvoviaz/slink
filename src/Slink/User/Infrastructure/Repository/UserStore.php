<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Repository;

use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\EventStore\AbstractStoreRepository;
use Slink\User\Domain\Repository\CheckUserByEmailInterface;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Email;
use Symfony\Contracts\Service\Attribute\Required;

final class UserStore extends AbstractStoreRepository implements UserStoreRepositoryInterface {
  private CheckUserByEmailInterface $checkUserByEmail;
  
  #[Required]
  public function setCheckUserByEmail(CheckUserByEmailInterface $checkUserByEmail): void {
    $this->checkUserByEmail = $checkUserByEmail;
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
   * @param Email $username
   * @return User|null
   * @throws \RuntimeException
   */
  #[\Override]
  public function getByUsername(Email $username): ?User {
    $id = $this->checkUserByEmail->existsEmail($username);
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
    $this->persist($user);
  }
}