<?php

declare(strict_types=1);

namespace Slink\User\Domain\Repository;

interface UserRoleRepositoryInterface {
  /**
   * @param string $role
   * @return bool
   */
  public function exists(string $role): bool;
}