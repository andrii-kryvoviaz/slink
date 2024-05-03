<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject;

use Ramsey\Collection\Set;
use Slink\Shared\Domain\ValueObject\AbstractValueObject;

final readonly class RoleSet extends AbstractValueObject {
  private Set $roles;
  
  /**
   * @param array<Role> $roles
   */
  private function __construct(array $roles) {
    $this->roles = new Set(Role::class, $roles);
  }
  
  /**
   * @param array<Role> $roles
   * @param array<Role> $defaultRoles
   * @return self
   */
  public static function create(array $roles = [], array $defaultRoles = []): self {
    if(empty($defaultRoles)) {
      $defaultRoles = [Role::fromString('ROLE_USER')];
    }
    
    return new self([...$roles, ...$defaultRoles]);
  }
  
  /**
   * @return array<Role>
   */
  public function getRoles(): array {
    return $this->roles->toArray();
  }
  
  /**
   * @return  array<string>
   */
  public function toArray(): array {
    return array_map(fn(Role $role) => $role->getRole(), $this->getRoles());
  }
  
  /**
   * @param Role $role
   * @return bool
   */
  public function contains(Role $role): bool {
    foreach($this->roles as $r) {
      if($r->getRole() === $role->getRole()) {
        return true;
      }
    }
    
    return false;
  }
  
  /**
   * @param Role $role
   */
  public function addRole(Role $role): void {
    if($this->contains($role)) {
      return;
    }
    
    $this->roles->add($role);
  }
  
  /**
   * @param Role $role
   */
  public function removeRole(Role $role): void {
    $this->roles->remove($role);
  }
}