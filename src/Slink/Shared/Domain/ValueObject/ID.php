<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\ValueObject;

use EventSauce\EventSourcing\AggregateRootId;
use Ramsey\Uuid\Uuid;

final readonly class ID implements \Stringable, AggregateRootId {
  private function __construct(
    private string $value,
  ) {}

  public static function fromString(string $value): static {
    return new self($value);
  }
  
public static function generate(): static {
    return new self(Uuid::uuid4()->toString());
  }

  public function toString(): string {
    return $this->value;
  }

  public function __toString() {
    return $this->toString();
  }
}