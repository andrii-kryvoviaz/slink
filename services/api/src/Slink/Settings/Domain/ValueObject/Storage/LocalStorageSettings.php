<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Storage;

use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;

final readonly class LocalStorageSettings extends AbstractCompoundValueObject {
  public function __construct(
    private string $directory,
  ) {}
  
  /**
   * @return array<string, mixed>
   */
  #[\Override]
  public function toPayload(): array {
    return [
      'dir' => $this->directory,
    ];
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return static
   */
  #[\Override]
  public static function fromPayload(array $payload): static {
    return new self(
      $payload['dir'],
    );
  }
}