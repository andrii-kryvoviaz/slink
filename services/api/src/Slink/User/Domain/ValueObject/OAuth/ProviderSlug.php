<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\OAuth;

use Slink\Shared\Domain\Exception\InvalidValueObjectException;
use Slink\Shared\Domain\ValueObject\AbstractStringValueObject;

final readonly class ProviderSlug extends AbstractStringValueObject {
  protected function __construct(string $value) {
    parent::__construct($value);
  }

  protected static function validate(string $value): void {
    parent::validate($value);

    if (strlen($value) > 50) {
      throw new InvalidValueObjectException(static::label(), 'must not exceed 50 characters');
    }

    if (!preg_match('/^[a-z][a-z0-9\-]{0,48}[a-z0-9]$/', $value)) {
      throw new InvalidValueObjectException(static::label(), 'must contain only lowercase letters, digits or hyphens');
    }
  }

  public static function fromString(string $value): self {
    return new self($value);
  }
}
