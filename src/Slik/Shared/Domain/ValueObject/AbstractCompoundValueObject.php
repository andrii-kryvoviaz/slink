<?php

declare(strict_types=1);

namespace Slik\Shared\Domain\ValueObject;

abstract readonly class AbstractCompoundValueObject extends AbstractValueObject{
  abstract public function toPayload(): array;
  
  abstract public static function fromPayload(array $payload): static;
  
  public function merge(self $other): self {
    return static::fromPayload(array_merge($this->toPayload(), $other->toPayload()));
  }
  
  public function toString(): string {
    return json_encode($this->toPayload());
  }
}