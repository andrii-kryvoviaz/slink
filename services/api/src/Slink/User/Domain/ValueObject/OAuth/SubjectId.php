<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\OAuth;

use Slink\Shared\Domain\ValueObject\AbstractStringValueObject;

final readonly class SubjectId extends AbstractStringValueObject {
  public static function fromString(string $value): self {
    return new self($value);
  }

  public static function fromStringOrNull(?string $value): ?self {
    if ($value === null) {
      return null;
    }

    return new self($value);
  }
}
