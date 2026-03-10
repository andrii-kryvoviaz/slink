<?php

declare(strict_types=1);

namespace Slink\User\Domain\Enum;

use Slink\Shared\Domain\Enum\ValidatorAwareEnumTrait;

enum DefaultVisibility: string {
  use ValidatorAwareEnumTrait;

  case Public = 'public';
  case Private = 'private';

  public function isPublic(): bool {
    return $this === self::Public;
  }
}
