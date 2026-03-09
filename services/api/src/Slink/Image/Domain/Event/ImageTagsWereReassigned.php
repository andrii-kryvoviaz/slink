<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Image\Domain\ValueObject\TagSet;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class ImageTagsWereReassigned implements SerializablePayload {
  public function __construct(
    public ID $imageId,
    public TagSet $tags,
    public ID $userId,
  ) {}

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'imageId' => $this->imageId->toString(),
      'tags' => $this->tags->toPayload(),
      'userId' => $this->userId->toString(),
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['imageId']),
      TagSet::fromPayload($payload['tags']),
      ID::fromString($payload['userId']),
    );
  }
}
