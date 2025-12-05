<?php

declare(strict_types=1);

namespace Slink\Bookmark\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class BookmarkWasCreated implements SerializablePayload {
  public function __construct(
    public ID $id,
    public ID $imageId,
    public ID $userId,
    public DateTime $createdAt,
  ) {
  }

  /**
   * @return array{uuid: string, imageId: string, userId: string, createdAt: string}
   */
  public function toPayload(): array {
    return [
      'uuid' => $this->id->toString(),
      'imageId' => $this->imageId->toString(),
      'userId' => $this->userId->toString(),
      'createdAt' => $this->createdAt->toString(),
    ];
  }

  /**
   * @param array{uuid: string, imageId: string, userId: string, createdAt: string} $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['uuid']),
      ID::fromString($payload['imageId']),
      ID::fromString($payload['userId']),
      DateTime::fromString($payload['createdAt']),
    );
  }
}
