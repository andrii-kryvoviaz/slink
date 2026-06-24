<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\ValueObject;

final readonly class BaseFileName {
  private function __construct(private string $value) {
  }

  public static function fromFileName(string $fileName): self {
    return new self(pathinfo($fileName, PATHINFO_FILENAME));
  }

  public function toString(): string {
    return $this->value;
  }

  public function __toString(): string {
    return $this->value;
  }
}
