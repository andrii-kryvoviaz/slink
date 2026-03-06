<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\ValueObject;

use Slink\Shared\Domain\Exception\InvalidValueObjectException;

abstract readonly class AbstractUriValueObject extends AbstractStringValueObject {
  protected static function validate(string $value): void {
    if ($value === '' || !filter_var($value, FILTER_VALIDATE_URL)) {
      throw new InvalidValueObjectException(static::label(), 'invalid URI format');
    }
  }
}
