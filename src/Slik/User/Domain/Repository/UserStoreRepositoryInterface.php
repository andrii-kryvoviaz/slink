<?php

declare(strict_types=1);

namespace Slik\User\Domain\Repository;

use Slik\Shared\Domain\ValueObject\ID;
use Slik\User\Domain\User;

interface UserStoreRepositoryInterface {
  public function get(ID $id): User;

  public function store(User $user): void;
}