<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject;

use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;

final readonly class LocalStorageSettings extends AbstractCompoundValueObject {
  
  /**
   * @return static
   */
  public static function create(): static {
    return new self();
  }
  
  /**
   * @return array<string, mixed>
   */
  #[\Override]
  public function toPayload(): array {
    return [];
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return static
   */
  #[\Override]
  public static function fromPayload(array $payload): static {
    return new self();
  }
}