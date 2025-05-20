<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Specification;

use Slink\User\Domain\Repository\UserRoleRepositoryInterface;
use Slink\User\Domain\Specification\UserRoleExistSpecificationInterface;
use Slink\User\Domain\ValueObject\Role;

final class UserRoleExistSpecification implements UserRoleExistSpecificationInterface {
  /**
   * @param UserRoleRepositoryInterface $repository
   */
  public function __construct(private UserRoleRepositoryInterface $repository) {}
  
  /**
   * @inheritDoc
   */
  public function isSatisfiedBy(Role $role): bool {
    return $this->repository->exists($role->getRole());
  }
}