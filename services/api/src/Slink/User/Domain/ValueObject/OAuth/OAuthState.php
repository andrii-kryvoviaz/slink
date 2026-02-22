<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\OAuth;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\User\Domain\Exception\InvalidOAuthValueException;

final readonly class OAuthState extends AbstractValueObject {
  private function __construct(private string $value) {
    if ($value === '') {
      throw new InvalidOAuthValueException('OAuthState', 'cannot be empty');
    }
  }

  public static function fromString(string $value): self {
    return new self($value);
  }

  public function toString(): string {
    return $this->value;
  }
}
