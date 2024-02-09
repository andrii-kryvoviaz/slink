<?php

declare(strict_types=1);

namespace Slink\User\Domain\Repository;

use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\User;

interface UserStoreRepositoryInterface {
  public function get(ID $id): User;

  public function store(User $user): void;
}