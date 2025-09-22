<?php

declare(strict_types=1);

namespace Slink\Tag\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class ImageWasUntagged implements SerializablePayload {
  public function __construct(
    public ID $imageId,
    public ID $tagId,
    public ID $userId,
  ) {}

  public function toPayload(): array {
    return [
      'image_id' => $this->imageId->toString(),
      'tag_id' => $this->tagId->toString(),
      'user_id' => $this->userId->toString(),
    ];
  }

  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['image_id']),
      ID::fromString($payload['tag_id']),
      ID::fromString($payload['user_id']),
    );
  }
}