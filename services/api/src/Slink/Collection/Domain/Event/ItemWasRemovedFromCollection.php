<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class ItemWasRemovedFromCollection implements SerializablePayload {
  public function __construct(
    public ID $collectionId,
    public ID $itemId,
  ) {
  }

  public function toPayload(): array {
    return [
      'collectionId' => $this->collectionId->toString(),
      'itemId' => $this->itemId->toString(),
    ];
  }

  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['collectionId']),
      ID::fromString($payload['itemId']),
    );
  }
}
