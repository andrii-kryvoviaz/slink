<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Enum;

enum ShareType: string {
  case ShortUrl = 'shortUrl';
  case Signed = 'signed';
}
