<?php

declare(strict_types=1);

namespace Slink\User\Domain\Enum;

use Slink\Shared\Domain\Enum\ValidatorAwareEnumTrait;

enum LandingPage: string {
  use ValidatorAwareEnumTrait;

  case Explore = 'explore';
  case Upload = 'upload';
}
