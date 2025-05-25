<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject;

use Slink\Shared\Domain\DataStructures\HashMap;
use Slink\Shared\Domain\ValueObject\AbstractValueObject;

final readonly class RoleSet extends AbstractValueObject {
  private HashMap $roles;
  
  /**
   * @param array<Role> $roles
   */
  private function __construct(array $roles) {
    $this->roles = new HashMap();
    
    foreach($roles as $role) {
      $this->addRole($role);
    }
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
    return array_values($this->roles->toArray());
  }
  
  /**
   * @return  array<string>
   */
  public function toArray(): array {
    return array_map(
      fn(Role $role) => $role->getRole(),
      $this->getRoles()
    );
  }
  
  /**
   * @param Role $role
   * @return bool
   */
  public function contains(Role $role): bool {
    return $this->roles->has($role->getRole());
  }
  
  /**
   * @param Role $role
   */
  public function addRole(Role $role): void {
    if($this->contains($role)) {
      return;
    }
    
    $this->roles->set($role->getRole(), $role);
  }
  
  /**
   * @param Role $role
   */
  public function removeRole(Role $role): void {
    $this->roles->remove($role->getRole());
  }
}