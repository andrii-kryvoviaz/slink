<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Enum;

use Slink\Shared\Domain\Enum\ValidatorAwareEnumTrait;

enum ColorMode: string {
  use ValidatorAwareEnumTrait;

  case Both = 'both';
  case Light = 'light';
  case Dark = 'dark';
}
