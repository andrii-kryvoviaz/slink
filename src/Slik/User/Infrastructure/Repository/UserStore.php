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
    return $this->retrieve($id);
  }

  public function store(User $user): void {
    $this->persist($user);
  }
}