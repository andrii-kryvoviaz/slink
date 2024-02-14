<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Specification;

use Doctrine\ORM\NonUniqueResultException;
use Slink\Shared\Domain\Specification\AbstractSpecification;
use Slink\User\Domain\Repository\CheckUserByEmailInterface;
use Slink\User\Domain\Specification\UniqueEmailSpecificationInterface;
use Slink\User\Domain\ValueObject\Email;

final class UniqueEmailSpecification extends AbstractSpecification implements UniqueEmailSpecificationInterface {
    public function __construct(private readonly CheckUserByEmailInterface $checkUserByEmail) {
    }
    
    public function isUnique(Email $email): bool {
        return $this->isSatisfiedBy($email);
    }

  /**
   * @param Email $value
   * @return bool
   * @psalm-suppress MoreSpecificImplementedParamType
   */
    public function isSatisfiedBy(mixed $value): bool {
        try {
          return !$this->checkUserByEmail->existsEmail($value);
        } catch (NonUniqueResultException) {
            return false;
        }
    }
}
