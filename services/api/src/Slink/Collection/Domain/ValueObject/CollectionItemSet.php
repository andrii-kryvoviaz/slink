<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\ValueObject;

use Slink\Collection\Domain\Enum\ItemType;
use Slink\Shared\Domain\DataStructures\HashMap;
use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class CollectionItemSet extends AbstractValueObject {
  private HashMap $items;

  private function __construct(array $items = []) {
    $this->items = new HashMap();

    foreach ($items as $item) {
      $this->addItem($item);
    }
  }

  public static function create(array $items = []): self {
    return new self($items);
  }

  public static function empty(): self {
    return new self();
  }

  private function addItem(CollectionItem $item): void {
    $this->items->set($item->getItemId()->toString(), $item);
  }

  public function add(CollectionItem $item): self {
    $items = $this->getItems();
    $items[] = $item;
    return new self($items);
  }

  public function remove(ID $itemId): self {
    $items = array_filter(
      $this->getItems(),
      fn(CollectionItem $item) => !$item->getItemId()->equals($itemId)
    );
    return new self(array_values($items));
  }

  public function contains(ID $itemId): bool {
    return $this->items->has($itemId->toString());
  }

  public function get(ID $itemId): ?CollectionItem {
    return $this->items->get($itemId->toString());
  }

  public function getItems(): array {
    return array_values($this->items->toArray());
  }

  public function getSortedItems(): array {
    $items = $this->getItems();
    usort($items, fn(CollectionItem $a, CollectionItem $b) => $a->getPosition() <=> $b->getPosition());
    return $items;
  }

  public function count(): int {
    return $this->items->count();
  }

  public function isEmpty(): bool {
    return $this->items->count() === 0;
  }

  public function getNextPosition(): float {
    if ($this->isEmpty()) {
      return 1.0;
    }

    $maxPosition = 0.0;
    foreach ($this->getItems() as $item) {
      if ($item->getPosition() > $maxPosition) {
        $maxPosition = $item->getPosition();
      }
    }

    return $maxPosition + 1.0;
  }

  public function reorder(array $orderedItemIds): self {
    $items = [];
    $position = 1.0;

    foreach ($orderedItemIds as $itemId) {
      $id = $itemId instanceof ID ? $itemId : ID::fromString($itemId);
      $existingItem = $this->get($id);

      if ($existingItem !== null) {
        $items[] = $existingItem->withPosition($position);
        $position += 1.0;
      }
    }

    return new self($items);
  }

  public function toPayload(): array {
    return array_map(
      fn(CollectionItem $item) => $item->toPayload(),
      $this->getItems()
    );
  }

  public static function fromPayload(array $payload): self {
    $items = array_map(
      fn(array $item) => CollectionItem::fromPayload($item),
      $payload
    );
    return new self($items);
  }
}
