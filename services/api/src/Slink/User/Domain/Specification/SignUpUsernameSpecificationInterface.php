<?php

declare(strict_types=1);

namespace Slink\User\Domain\Specification;

use Slink\User\Domain\Exception\InvalidUsernameException;

interface SignUpUsernameSpecificationInterface {
  public function isSatisfiedBy(string $username): bool;

  /**
   * @throws InvalidUsernameException naming the first sign-up rule the username breaks
   */
  public function validate(string $username): void;
}
