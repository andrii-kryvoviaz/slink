<?php

declare(strict_types=1);

namespace Slink\User\Domain\Enum;

use Slink\Shared\Domain\Enum\ValidatorAwareEnumTrait;

enum UserStatus: string {
  use ValidatorAwareEnumTrait;
  
  case Active = 'active';
  case Inactive = 'inactive';
  case Suspended = 'suspended';
  case Banned = 'banned';
  case Deleted = 'deleted';
  
  private function equals(UserStatus $status): bool {
    return $this->value === $status->value;
  }
  
  public function isActive(): bool {
    return $this->equals(self::Active);
  }
  
  public function isInactive(): bool {
    return $this->equals(self::Inactive);
  }
  
  public function isSuspended(): bool {
    return $this->equals(self::Suspended);
  }
  
  public function isBanned(): bool {
    return $this->equals(self::Banned);
  }
  
  public function isDeleted(): bool {
    return $this->equals(self::Deleted);
  }
  
  public function isRestricted(): bool {
    return $this->isSuspended() || $this->isBanned() || $this->isInactive() || $this->isDeleted();
  }
}