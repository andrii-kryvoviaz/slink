<?php

declare(strict_types=1);

namespace Slink\User\Domain\Specification;

use Slink\User\Domain\ValueObject\Role;

interface UserRoleExistSpecificationInterface {
  /**
   * @param Role $role
   * @return bool
   */
  public function isSatisfiedBy(Role $role): bool;
}