<?php

declare(strict_types=1);

namespace Slink\User\Domain\Context;

use Override;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Specification\CurrentUserSpecificationInterface;
use Slink\User\Domain\Specification\UserRoleExistSpecificationInterface;

final readonly class SystemChangeUserRoleContext extends ChangeUserRoleContext {
  public function __construct(
    UserRoleExistSpecificationInterface $userRoleExistSpecification
  ) {
    parent::__construct(
      $userRoleExistSpecification,
      new class implements CurrentUserSpecificationInterface {
        #[Override]
        public function isSatisfiedBy(mixed $value): bool {
          return false;
        }

        #[Override]
        public function isSameUser(ID $id): bool {
          return false;
        }
      }
    );
  }
}
