<?php

declare(strict_types=1);

namespace Slik\User\Domain\Specification;

use Slik\User\Domain\Exception\EmailAlreadyExistException;
use Slik\User\Domain\ValueObject\Email;

interface UniqueEmailSpecificationInterface {
  /**
   * @throws EmailAlreadyExistException
   */
  public function isUnique(Email $email): bool;
}