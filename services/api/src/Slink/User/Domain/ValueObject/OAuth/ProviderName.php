<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\OAuth;

use Slink\Shared\Domain\Exception\InvalidValueObjectException;
use Slink\Shared\Domain\ValueObject\AbstractStringValueObject;

final readonly class ProviderName extends AbstractStringValueObject {
  protected function __construct(string $value) {
    parent::__construct($value);
  }

  protected static function validate(string $value): void {
    parent::validate($value);

    if (strlen($value) > 100) {
      throw new InvalidValueObjectException(static::label(), 'must not exceed 100 characters');
    }
  }

  public static function fromString(string $value): self {
    return new self($value);
  }
}
