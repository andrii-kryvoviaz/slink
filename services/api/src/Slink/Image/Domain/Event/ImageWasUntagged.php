<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class ImageWasUntagged implements SerializablePayload {
  public function __construct(
    public ID $imageId,
    public ID $tagId,
    public ID $userId,
  ) {}

  /**
   * @return array<string, string>
   */
  public function toPayload(): array {
    return [
      'imageId' => $this->imageId->toString(),
      'tagId' => $this->tagId->toString(),
      'userId' => $this->userId->toString(),
    ];
  }

  /**
   * @param array<string, string> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['imageId']),
      ID::fromString($payload['tagId']),
      ID::fromString($payload['userId']),
    );
  }
}