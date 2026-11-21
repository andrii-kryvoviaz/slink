<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\FloatType as DoctrineFloatType;

final class FloatType extends DoctrineFloatType {
  public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed {
    if (\is_float($value)) {
      return \var_export($value, true);
    }

    return $value;
  }
}
