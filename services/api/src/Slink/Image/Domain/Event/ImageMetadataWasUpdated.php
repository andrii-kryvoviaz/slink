<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class ImageMetadataWasUpdated implements SerializablePayload {
  public function __construct(
    public ID            $id,
    public ImageMetadata $metadata
  ) {
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'uuid' => $this->id->toString(),
      'metadata' => $this->metadata->toPayload(),
    ];
  }

  /**
   * @param array<string, mixed> $payload
   * @throws DateTimeException
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['uuid']),
      ImageMetadata::fromPayload($payload['metadata'])
    );
  }
}