<?php

declare(strict_types=1);

namespace Slink\User\Domain\Enum;

use Slink\Shared\Domain\Enum\ValidatorAwareEnumTrait;

enum UserRole: string {
  use ValidatorAwareEnumTrait;
  
  case Admin = 'ROLE_ADMIN';
  
  case User = 'ROLE_USER';
  
  public function getRole(): string {
    return $this->value;
  }
}
