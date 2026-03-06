<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\OAuth;

use Slink\Shared\Domain\Exception\InvalidValueObjectException;
use Slink\Shared\Domain\ValueObject\AbstractStringValueObject;

final readonly class OAuthScopes extends AbstractStringValueObject {
  protected function __construct(string $value) {
    parent::__construct($value);
  }

  protected static function validate(string $value): void {
    parent::validate($value);

    if (strlen($value) > 255) {
      throw new InvalidValueObjectException(static::label(), 'must not exceed 255 characters');
    }

    if (!preg_match('/^[a-zA-Z0-9_\-\.\s]+$/', $value)) {
      throw new InvalidValueObjectException(static::label(), 'contains invalid characters');
    }
  }

  public static function fromString(string $value): self {
    return new self($value);
  }
}
