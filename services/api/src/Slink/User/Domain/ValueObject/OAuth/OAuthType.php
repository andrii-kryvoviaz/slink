<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\OAuth;

use Slink\Shared\Domain\Exception\InvalidValueObjectException;
use Slink\Shared\Domain\ValueObject\AbstractStringValueObject;

final readonly class OAuthType extends AbstractStringValueObject {
  protected function __construct(string $value) {
    parent::__construct($value);
  }

  protected static function validate(string $value): void {
    parent::validate($value);

    if ($value !== 'oidc') {
      throw new InvalidValueObjectException(static::label(), 'must be "oidc"');
    }
  }

  public static function fromString(string $value): self {
    return new self($value);
  }
}
