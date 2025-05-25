<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Specification;

use Slink\Shared\Domain\Specification\AbstractSpecification;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Specification\CurrentUserSpecificationInterface;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Bundle\SecurityBundle\Security;

final class CurrentUserSpecification extends AbstractSpecification implements CurrentUserSpecificationInterface {
  
  public function __construct(
    private readonly Security $security
  ) {
  }
  
  /**
   * @return ID|null
   */
  private function getCurrentUserId(): ?ID {
    /** @var JwtUser|null $user */
    $user = $this->security->getUser();
    
    return ID::fromUnknown($user?->getIdentifier());
  }
  
  /**
   * @param ID $id
   * @return bool
   */
  public function isSameUser(ID $id): bool {
    return $id->equals($this->getCurrentUserId());
  }
  
  /**
   * @param ID $id
   * @return bool
   */
  public function isSatisfiedBy(mixed $id): bool {
    return $this->isSameUser($id);
  }
}
