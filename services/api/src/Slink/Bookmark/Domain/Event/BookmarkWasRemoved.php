<?php

declare(strict_types=1);

namespace Slink\Bookmark\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class BookmarkWasRemoved implements SerializablePayload {
  public function __construct(
    public ID $id,
    public DateTime $removedAt,
  ) {
  }

  public function toPayload(): array {
    return [
      'uuid' => $this->id->toString(),
      'removedAt' => $this->removedAt->toString(),
    ];
  }

  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['uuid']),
      DateTime::fromString($payload['removedAt']),
    );
  }
}
