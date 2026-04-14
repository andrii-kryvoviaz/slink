<?php

declare(strict_types=1);

namespace Slink\User\Domain\Enum;

use Slink\Shared\Domain\Enum\ValidatorAwareEnumTrait;

enum DisplayLanguage: string {
  use ValidatorAwareEnumTrait;

  case En = 'en';
  case De = 'de';
  case Es = 'es';
  case Fr = 'fr';
  case It = 'it';
  case Pl = 'pl';
  case Uk = 'uk';
  case Zh = 'zh';
}
