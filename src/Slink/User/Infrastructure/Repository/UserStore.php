<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Repository;

use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\EventStore\AbstractStoreRepository;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\User;

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