<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Enum;

enum ShareExpiryFilter: string {
  case Any = 'any';
  case HasExpiry = 'hasExpiry';
  case Expired = 'expired';
  case NoExpiry = 'noExpiry';
}
