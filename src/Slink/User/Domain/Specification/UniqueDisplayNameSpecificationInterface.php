<?php

declare(strict_types=1);

namespace Slink\User\Domain\Specification;

use Slink\User\Domain\Exception\DisplayNameAlreadyExistException;
use Slink\User\Domain\ValueObject\DisplayName;

interface UniqueDisplayNameSpecificationInterface {
  /**
   * @throws DisplayNameAlreadyExistException
   */
  public function isUnique(DisplayName $displayName): bool;
}