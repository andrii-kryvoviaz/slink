<?php

declare(strict_types=1);

namespace Slik\Shared\Domain\ValueObject;

use EventSauce\EventSourcing\AggregateRootId;

final readonly class ID implements \Stringable, AggregateRootId {
  private function __construct(
    private string $value,
  ) {}

  public static function fromString(string $value): static {
    return new self($value);
  }

  public function toString(): string {
    return $this->value;
  }

  public function __toString() {
    return $this->toString();
  }
}