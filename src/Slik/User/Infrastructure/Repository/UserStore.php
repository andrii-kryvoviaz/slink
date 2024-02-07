<?php

declare(strict_types=1);

namespace Slik\User\Infrastructure\Repository;

use Slik\Shared\Domain\ValueObject\ID;
use Slik\Shared\Infrastructure\Persistence\EventStore\AbstractStoreRepository;
use Slik\User\Domain\Repository\UserStoreRepositoryInterface;
use Slik\User\Domain\User;

final class UserStore extends AbstractStoreRepository implements UserStoreRepositoryInterface {
  static function getAggregateRootClass(): string {
    return User::class;
  }

  public function get(ID $id): User {
    $user = $this->retrieve($id);
    if (!$user instanceof User) {
      throw new \RuntimeException('Expected instance of User, got ' . get_class($user));
    }
    return $user;
  }

  public function store(User $user): void {
    $this->persist($user);
  }
}