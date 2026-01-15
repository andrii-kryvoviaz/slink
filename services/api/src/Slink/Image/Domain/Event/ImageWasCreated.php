<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Image\Domain\Enum\License;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class ImageWasCreated implements SerializablePayload {
  /**
   * @param ID $id
   * @param ID|null $userId
   * @param ImageAttributes $attributes
   * @param ImageMetadata|null $metadata
   * @param License|null $license
   */
  public function __construct(
    public ID $id,
    public ?ID $userId,
    public ImageAttributes $attributes,
    public ?ImageMetadata $metadata = null,
    public ?License $license = null,
  ) {
  }
  
  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'uuid' => $this->id->toString(),
      'user' => $this->userId?->toString(),
      'attributes' => $this->attributes->toPayload(),
      ...($this->metadata ? ['metadata' => $this->metadata->toPayload()] : []),
      ...($this->license ? ['license' => $this->license->value] : []),
    ];
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return static
   * @throws DateTimeException
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['uuid']),
      $payload['user'] ? ID::fromString($payload['user']) : null,
      ImageAttributes::fromPayload($payload['attributes']),
      isset($payload['metadata']) ? ImageMetadata::fromPayload($payload['metadata']) : null,
      isset($payload['license']) ? License::tryFrom($payload['license']) : null,
    );
  }
}