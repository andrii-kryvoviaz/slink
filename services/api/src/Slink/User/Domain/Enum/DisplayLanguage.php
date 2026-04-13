<?php

declare(strict_types=1);

namespace Slink\User\Domain\Enum;

use Slink\Shared\Domain\Enum\ValidatorAwareEnumTrait;

enum DisplayLanguage: string {
  use ValidatorAwareEnumTrait;

  case En = 'en';
  case Uk = 'uk';
  case De = 'de';
  case Zh = 'zh';
}
