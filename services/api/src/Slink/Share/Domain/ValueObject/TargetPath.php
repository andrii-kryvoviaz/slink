<?php

declare(strict_types=1);

namespace Slink\Share\Domain\ValueObject;

use Slink\Shared\Domain\Exception\InvalidValueObjectException;
use Slink\Shared\Domain\ValueObject\AbstractStringValueObject;

final readonly class TargetPath extends AbstractStringValueObject {
  public static function fromString(string $value): self {
    return new self($value);
  }

  protected static function validate(string $value): void {
    parent::validate($value);

    if ($value[0] !== '/') {
      throw new InvalidValueObjectException(static::label(), 'must start with "/"');
    }
  }
}
