<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\OAuth;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\User\Domain\Exception\InvalidOAuthValueException;

final readonly class RedirectUri extends AbstractValueObject {
  private function __construct(private string $value) {
    if ($value === '' || !filter_var($value, FILTER_VALIDATE_URL)) {
      throw new InvalidOAuthValueException('RedirectUri', 'invalid URI format');
    }
  }

  public static function fromString(string $value): self {
    return new self($value);
  }

  public function toString(): string {
    return $this->value;
  }
}
