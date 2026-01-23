<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class CollectionItemsWereReordered implements SerializablePayload {
  /**
   * @param array<string> $orderedItemIds
   */
  public function __construct(
    public ID $collectionId,
    public array $orderedItemIds,
  ) {
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'collectionId' => $this->collectionId->toString(),
      'orderedItemIds' => $this->orderedItemIds,
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['collectionId']),
      $payload['orderedItemIds'],
    );
  }
}
