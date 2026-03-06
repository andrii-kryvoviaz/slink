<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Enum;

enum SortDirection: string {
  use ValidatorAwareEnumTrait;

  case Up = 'up';
  case Down = 'down';
}
