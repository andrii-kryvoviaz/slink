<?php

declare(strict_types=1);

namespace Slink\User\Domain\Enum;

enum RevocationScope: string {
  case None = 'none';
  case IssuedBefore = 'issued_before';
  case All = 'all';
}
