<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class ImageWasDeleted implements SerializablePayload {
  public function __construct(
    public ID $id,
    public bool $preserveOnDisk,
  ) {
  }
  
  /**
   * @return array<string, mixed>
   */
  #[\Override]
  public function toPayload(): array {
    return [
      'id' => $this->id->toString(),
      'preserveOnDisk' => $this->preserveOnDisk,
    ];
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return static
   */
  #[\Override]
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['id']),
      $payload['preserveOnDisk'],
    );
  }
}