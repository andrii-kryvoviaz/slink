<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\OAuth;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\User\Domain\Exception\InvalidOAuthValueException;

final readonly class PkceVerifier extends AbstractValueObject {
  private function __construct(private string $value) {
    if ($value === '') {
      throw new InvalidOAuthValueException('PkceVerifier', 'cannot be empty');
    }
  }

  public static function fromString(string $value): self {
    return new self($value);
  }

  public static function fromNullableString(?string $value): ?self {
    return $value !== null ? new self($value) : null;
  }

  public function toString(): string {
    return $this->value;
  }
}
