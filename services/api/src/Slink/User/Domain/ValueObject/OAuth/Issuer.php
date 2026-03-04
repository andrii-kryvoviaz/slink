<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\OAuth;

use Slink\Shared\Domain\ValueObject\AbstractUriValueObject;

final readonly class Issuer extends AbstractUriValueObject {
  public static function fromString(?string $value): ?self {
    if ($value === null) {
      return null;
    }

    return new self($value);
  }
}
