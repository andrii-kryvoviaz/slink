<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\RevokeRole;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\ValueObject\Role;

final readonly class RevokeRoleCommand implements CommandInterface {
  /**
   * @param string $id
   * @param string $role
   */
  public function __construct(
    private string $id,
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