<?php

declare(strict_types=1);

namespace Slink\User\Domain\Enum;

enum UserStatus: string {
  case Active = 'active';
  case Inactive = 'inactive';
  case Suspended = 'suspended';
  case Banned = 'banned';
}