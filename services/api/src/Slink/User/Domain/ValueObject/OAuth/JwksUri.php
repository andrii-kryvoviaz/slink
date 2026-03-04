<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\OAuth;

use Slink\Shared\Domain\ValueObject\AbstractUriValueObject;

final readonly class JwksUri extends AbstractUriValueObject {
  public static function fromString(string $value): self {
    return new self($value);
  }
}
