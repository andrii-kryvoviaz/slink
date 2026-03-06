<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\OAuth;

use Slink\Shared\Domain\ValueObject\AbstractUriValueObject;

final readonly class DiscoveryUrl extends AbstractUriValueObject {
  protected function __construct(string $value) {
    parent::__construct($value);
  }

  public static function fromString(string $value): self {
    return new self($value);
  }
}
