<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;

final readonly class Role extends AbstractValueObject {
  /**
   * @param string $role
   */
  private function __construct(
    private string $role
  ) {
  }
  
  /**
   * @param string $role
   * @return self
   */
  public static function fromString(string $role): self {
    $role = self::formatRole($role);
    return new self($role);
  }
  
  /**
   * @return string
   */
  public function getRole(): string {
    return $this->role;
  }
  
  /**
   * @param string $role
   * @return string
   */
  private static function formatRole(string $role): string {
    if(!str_starts_with($role, 'ROLE_')) {
      return 'ROLE_' . strtoupper($role);
    }
    
    return strtoupper($role);
  }
}