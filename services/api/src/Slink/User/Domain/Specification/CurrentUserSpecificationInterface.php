<?php

declare(strict_types=1);

namespace Slink\User\Domain\Specification;

use Slink\Shared\Domain\ValueObject\ID;

interface CurrentUserSpecificationInterface {
  /**
   * @param mixed $value
   * @return bool
   */
  public function isSatisfiedBy(mixed $value): bool;
  
  /**
   * @param ID $id
   * @return bool
   */
  public function isSameUser(ID $id): bool;
}
