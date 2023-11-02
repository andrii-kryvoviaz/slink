<?php

declare(strict_types=1);

namespace Slik\Shared\Domain\ValueObject;

abstract readonly class AbstractCompoundValueObject {
  abstract public function toPayload(): array;
  
  abstract public static function fromPayload(array $payload): static;
  
  public function equals(self $other): bool {
    return $this->toPayload() === $other->toPayload();
  }
  
  public function __toString(): string {
    return json_encode($this->toPayload());
  }
  
  public function merge(self $other): self {
    return static::fromPayload(array_merge($this->toPayload(), $other->toPayload()));
  }
}