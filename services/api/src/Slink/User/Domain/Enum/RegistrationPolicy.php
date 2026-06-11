<?php

declare(strict_types=1);

namespace Slink\User\Domain\Enum;

use Slink\Shared\Domain\Enum\ValidatorAwareEnumTrait;

enum RegistrationPolicy: string {
  use ValidatorAwareEnumTrait;

  case Inherit = 'inherit';
  case Allowed = 'allowed';
  case Blocked = 'blocked';

  public function resolves(bool $globalValue): bool {
    return match ($this) {
      self::Inherit => $globalValue,
      self::Allowed => true,
      self::Blocked => false,
    };
  }
}
