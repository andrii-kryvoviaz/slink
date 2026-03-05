<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\OAuth;

use Slink\Shared\Domain\ValueObject\AbstractStringValueObject;

final readonly class IdToken extends AbstractStringValueObject {
  protected function __construct(#[\SensitiveParameter] string $value) {
    parent::__construct($value);
  }

  public static function fromString(#[\SensitiveParameter] string $value): self {
    return new self($value);
  }

  /**
   * @param array<string, mixed> $values
   */
  public static function fromPayload(array $values): ?self {
    return !empty($values['id_token']) ? self::fromString($values['id_token']) : null;
  }
}
