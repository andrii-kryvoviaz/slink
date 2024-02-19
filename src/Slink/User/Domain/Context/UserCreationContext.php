<?php

declare(strict_types=1);

namespace Slink\User\Domain\Context;

use Slink\User\Domain\Specification\UniqueDisplayNameSpecificationInterface;
use Slink\User\Infrastructure\Specification\UniqueEmailSpecification;

final readonly class UserCreationContext {
  public function __construct(
    public UniqueEmailSpecification $uniqueEmailSpecification,
    public UniqueDisplayNameSpecificationInterface $uniqueDisplayNameSpecification
  ) {
  }
}