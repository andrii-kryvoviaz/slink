<?php

declare(strict_types=1);

namespace Slink\User\Domain\Context;

use Slink\User\Domain\Specification\CurrentUserSpecificationInterface;
use Slink\User\Domain\Specification\UserRoleExistSpecificationInterface;

final readonly class ChangeUserRoleContext {
  public function __construct(
    public UserRoleExistSpecificationInterface $userRoleExistSpecification,
    public CurrentUserSpecificationInterface $currentUserSpecification
  ) {
  }
}
