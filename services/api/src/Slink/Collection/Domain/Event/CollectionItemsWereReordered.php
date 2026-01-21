<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class CollectionItemsWereReordered implements SerializablePayload {
  public function __construct(
    public ID $collectionId,
    public array $orderedItemIds,
  ) {
  }

  public function toPayload(): array {
    return [
      'collectionId' => $this->collectionId->toString(),
      'orderedItemIds' => $this->orderedItemIds,
    ];
  }

  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['collectionId']),
      $payload['orderedItemIds'],
    );
  }
}
