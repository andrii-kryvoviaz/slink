<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class CollectionWasDeleted implements SerializablePayload {
  public function __construct(
    public ID $id,
    public DateTime $deletedAt,
  ) {
  }

  public function toPayload(): array {
    return [
      'id' => $this->id->toString(),
      'deletedAt' => $this->deletedAt->toString(),
    ];
  }

  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['id']),
      DateTime::fromString($payload['deletedAt']),
    );
  }
}
