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
   * @param mixed $value
   * @return static|null
   */
  public static function fromUnknown(mixed $value): ?static {
    if (\is_null($value)) {
      return null;
    }
    
    return self::fromString((string) $value);
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
  public function getValue(): string {
    return $this->value;
  }
  
  /**
   * @return string
   */
  #[\Override]
  public function toString(): string {
    return $this->getValue();
  }

  public function __toString(): string {
    return $this->getValue();
  }
}
