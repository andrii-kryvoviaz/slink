<?php

declare(strict_types=1);

namespace Slink\User\Domain\Enum;

use Slink\Shared\Domain\Enum\ValidatorAwareEnumTrait;

enum DisplayTheme: string {
  use ValidatorAwareEnumTrait;

  case Default = 'default';
  case Nord = 'nord';
  case Catppuccin = 'catppuccin';
  case Gruvbox = 'gruvbox';
  case RosePine = 'rose-pine';
  case TokyoNight = 'tokyo-night';
  case Everforest = 'everforest';
  case Monochrome = 'monochrome';
}
