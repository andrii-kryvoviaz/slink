<?php

declare(strict_types=1);

namespace Slik\User\Domain\ValueObject;

use Slik\Shared\Domain\ValueObject\AbstractValueObject;

final readonly class DisplayName extends AbstractValueObject {

  private function __construct(private string $displayName) {
  }

  public static function fromString(string $displayName): self {
    self::validate($displayName);

    return new self($displayName);
  }

  public function toString(): string {
    return $this->displayName ?? '';
  }

  private static function validate(string $displayName): void {
    if (strlen($displayName) < 3) {
      throw new \InvalidArgumentException('Display name must be at least 3 characters long');
    }
  }
}