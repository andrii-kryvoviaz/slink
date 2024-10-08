<?php

declare(strict_types=1);

namespace Slink\User\Domain\Context;

use Slink\User\Domain\Specification\UniqueDisplayNameSpecificationInterface;
use Slink\User\Domain\Specification\UniqueEmailSpecificationInterface;
use Slink\User\Domain\Specification\UniqueUsernameSpecificationInterface;

final readonly class UserCreationContext {
  public function __construct(
    public UniqueEmailSpecificationInterface $uniqueEmailSpecification,
    public UniqueUsernameSpecificationInterface $uniqueUsernameSpecification,
    public UniqueDisplayNameSpecificationInterface $uniqueDisplayNameSpecification
  ) {
  }
}