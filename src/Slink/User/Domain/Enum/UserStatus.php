<?php

declare(strict_types=1);

namespace Slink\User\Domain\Enum;

enum UserStatus: string {
  case Active = 'active';
  case Inactive = 'inactive';
  case Suspended = 'suspended';
  case Banned = 'banned';
  
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
}