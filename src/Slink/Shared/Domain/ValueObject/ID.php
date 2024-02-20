<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\ValueObject;

use EventSauce\EventSourcing\AggregateRootId;
use Ramsey\Uuid\Uuid;

final readonly class ID extends AbstractValueObject implements AggregateRootId {
  /**
   * @param string $value
   */
  private function __construct(
    private string $value,
  ) {}
  
  /**
   * @param string $value
   * @return static
   */
  public static function fromString(string $value): static {
    return new self($value);
  }
  
  /**
   * @return static
   */
  public static function generate(): static {
    return new self(Uuid::uuid4()->toString());
  }
  
  /**
   * @return string
   */
  #[\Override]
  public function toString(): string {
    return $this->value;
  }
}