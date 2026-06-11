<?php

declare(strict_types=1);

namespace Slink\User\Domain\Enum;

use Slink\Shared\Domain\Enum\ValidatorAwareEnumTrait;

enum ApprovalPolicy: string {
  use ValidatorAwareEnumTrait;

  case Inherit = 'inherit';
  case Required = 'required';
  case None = 'none';

  public function resolves(bool $globalValue): bool {
    return match ($this) {
      self::Inherit => $globalValue,
      self::Required => true,
      self::None => false,
    };
  }
}
