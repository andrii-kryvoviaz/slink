<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Enum;

enum ImageFilter: string {
  case Dramatic = 'dramatic';
  case Noir = 'noir';
  case Sepia = 'sepia';
  case Warm = 'warm';
  case Cool = 'cool';
  case Vivid = 'vivid';
  case Fade = 'fade';

  public static function tryFromString(?string $value): ?self {
    if ($value === null) {
      return null;
    }

    return self::tryFrom(strtolower($value));
  }
}
