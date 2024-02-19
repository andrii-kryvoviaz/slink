<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Specification;

use Slink\User\Domain\Repository\CheckUserByDisplayNameInterface;
use Slink\User\Domain\Specification\UniqueDisplayNameSpecificationInterface;
use Slink\User\Domain\ValueObject\DisplayName;

final readonly class UniqueDisplayNameSpecification implements UniqueDisplayNameSpecificationInterface {
  public function __construct(private CheckUserByDisplayNameInterface $checkUserByDisplayName) {
  }
  
  /**
   * @inheritDoc
   */
  #[\Override]
  public function isUnique(DisplayName $displayName): bool {
    return $this->isSatisfiedBy($displayName);
  }
  
  /**
   * @param DisplayName $value
   * @return bool
   * @psalm-suppress MoreSpecificImplementedParamType
   */
  public function isSatisfiedBy(mixed $value): bool {
    try {
      return !$this->checkUserByDisplayName->existsDisplayName($value);
    } catch (NonUniqueResultException) {
      return false;
    }
  }
}