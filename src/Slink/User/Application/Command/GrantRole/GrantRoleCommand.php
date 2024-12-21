<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\GrantRole;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Enum\UserRole;
use Slink\User\Domain\ValueObject\Role;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class GrantRoleCommand implements CommandInterface {
  /**
   * @param string $id
   * @param string $role
   */
  public function __construct(
    #[Assert\NotBlank]
    private string $id,
    
    #[Assert\NotBlank]
    #[Assert\Choice(callback: [UserRole::class, 'values'])]
    private string $role
  ) {
  }
  
  /**
   * @return ID
   */
  public function getId(): ID {
    return ID::fromString($this->id);
  }
  
  /**
   * @return Role
   */
  public function getRole(): Role {
    return Role::fromString($this->role);
  }
}
