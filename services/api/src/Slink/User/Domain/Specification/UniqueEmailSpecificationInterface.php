<?php

declare(strict_types=1);

namespace Slink\User\Domain\Specification;

use Slink\User\Domain\Exception\EmailAlreadyExistException;
use Slink\User\Domain\ValueObject\Email;

interface UniqueEmailSpecificationInterface {
  /**
   * @throws EmailAlreadyExistException
   */
  public function isUnique(Email $email): bool;
}