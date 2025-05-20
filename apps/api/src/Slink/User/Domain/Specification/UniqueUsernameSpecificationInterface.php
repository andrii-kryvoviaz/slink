<?php

declare(strict_types=1);

namespace Slink\User\Domain\Specification;

use Slink\User\Domain\Exception\UsernameAlreadyExistException;
use Slink\User\Domain\ValueObject\Username;

interface UniqueUsernameSpecificationInterface {
  /**
   * @throws UsernameAlreadyExistException
   */
  public function isUnique(Username $username): bool;
}