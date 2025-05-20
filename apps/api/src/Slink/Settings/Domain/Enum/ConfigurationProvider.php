<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Enum;

use Slink\Shared\Domain\Enum\ValidatorAwareEnumTrait;

enum ConfigurationProvider: string {
  use ValidatorAwareEnumTrait;
  
  case Default = 'default';
  case Store = 'store';
}
