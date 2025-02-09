<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\GrantRole;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\User\Domain\Enum\UserRole;
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
   * @return string
   */
  public function getId(): string {
    return $this->id;
  }
  
  /**
   * @return string
   */
  public function getRole(): string {
    return $this->role;
  }
}
