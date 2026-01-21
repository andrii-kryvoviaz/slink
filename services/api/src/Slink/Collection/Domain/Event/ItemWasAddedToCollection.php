<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Collection\Domain\ValueObject\CollectionItem;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class ItemWasAddedToCollection implements SerializablePayload {
  public function __construct(
    public ID $collectionId,
    public CollectionItem $item,
  ) {
  }

  public function toPayload(): array {
    return [
      'collectionId' => $this->collectionId->toString(),
      'item' => $this->item->toPayload(),
    ];
  }

  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['collectionId']),
      CollectionItem::fromPayload($payload['item']),
    );
  }
}
