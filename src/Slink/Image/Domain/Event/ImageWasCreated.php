<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Shared\Domain\Exception\DateTimeException;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class ImageWasCreated implements SerializablePayload {
  /**
   * @param ID $id
   * @param ID $userId
   * @param ImageAttributes $attributes
   * @param ImageMetadata|null $metadata
   */
  public function __construct(
    public ID $id,
    public ID $userId,
    public ImageAttributes $attributes,
    public ?ImageMetadata $metadata = null,
  ) {
  }
  
  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'id' => $this->id->toString(),
      'userId' => $this->userId->toString(),
      'attributes' => $this->attributes->toPayload(),
      ...($this->metadata? ['metadata' => $this->metadata->toPayload()] : []),
    ];
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return static
   * @throws DateTimeException
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['id']),
      ID::fromString($payload['userId']),
      ImageAttributes::fromPayload($payload['attributes']),
      $payload['metadata']? ImageMetadata::fromPayload($payload['metadata']) : null,
    );
  }
}