<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Specification;

use Doctrine\ORM\NonUniqueResultException;
use Slink\User\Domain\Repository\CheckUserByUsernameInterface;
use Slink\User\Domain\Specification\UniqueUsernameSpecificationInterface;
use Slink\User\Domain\ValueObject\Username;

final readonly class UniqueUsernameSpecification implements UniqueUsernameSpecificationInterface {
  
  public function __construct(private CheckUserByUsernameInterface $checkUserByUsername) {
  }
  
  /**
   * @inheritDoc
   */
  public function isUnique(Username $username): bool {
    return $this->isSatisfiedBy($username);
  }
  
  /**
   * @param Username $value
   * @return bool
   * @psalm-suppress MoreSpecificImplementedParamType
   */
  public function isSatisfiedBy(mixed $value): bool {
    try {
      return !$this->checkUserByUsername->existsUsername($value);
    } catch (NonUniqueResultException) {
      return false;
    }
  }
}