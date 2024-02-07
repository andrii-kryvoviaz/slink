<?php

declare(strict_types=1);

namespace Slik\Shared\Domain\ValueObject;

abstract readonly class AbstractCompoundValueObject extends AbstractValueObject{
  /**
   * @return array<string, mixed>
   */
  abstract public function toPayload(): array;
  
  /**
   * @param array<string, mixed> $payload
   * @return static
   */
  abstract public static function fromPayload(array $payload): static;
  
  /**
   * @param AbstractCompoundValueObject $other
   * @return static
   */
  public function merge(self $other): self {
    return static::fromPayload(array_merge($this->toPayload(), $other->toPayload()));
  }
  
  /**
   * @return string
   */
  public function toString(): string {
    return (string) json_encode($this->toPayload());
  }
}