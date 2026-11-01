<?php

declare(strict_types=1);

namespace Slink\Collection\Domain;

use Slink\Collection\Domain\Enum\ItemType;
use Slink\Collection\Domain\Event\CollectionItemsWereReordered;
use Slink\Collection\Domain\Event\CollectionWasCreated;
use Slink\Collection\Domain\Event\CollectionWasDeleted;
use Slink\Collection\Domain\Event\CollectionWasUpdated;
use Slink\Collection\Domain\Event\ItemWasAddedToCollection;
use Slink\Collection\Domain\Event\ItemWasRemovedFromCollection;
use Slink\Collection\Domain\ValueObject\CollectionDescription;
use Slink\Collection\Domain\ValueObject\CollectionItem;
use Slink\Collection\Domain\ValueObject\CollectionItemSet;
use Slink\Collection\Domain\ValueObject\CollectionName;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionView;
use Slink\Shared\Domain\AbstractAggregateRoot;
use Slink\Shared\Domain\Contract\OwnerAwareInterface;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final class Collection extends AbstractAggregateRoot implements OwnerAwareInterface {
  private ID $userId;
  private CollectionName $name;
  private CollectionDescription $description;
  private CollectionItemSet $items;
  private DateTime $createdAt;
  private ?DateTime $updatedAt = null;

  protected function __construct(ID $id) {
    parent::__construct($id);
    $this->items = CollectionItemSet::empty();
  }

  public static function create(
    ID $id,
    ID $userId,
    CollectionName $name,
    CollectionDescription $description,
  ): self {
    $collection = new self($id);
    $now = DateTime::now();

    $collection->recordThat(new CollectionWasCreated(
      $id,
      $userId,
      $name,
      $description,
      $now,
    ));

    return $collection;
  }

  /**
   * @param list<CollectionItem> $items
   */
  public static function fromView(CollectionView $view, array $items): self {
    $collection = new self(ID::fromString($view->getUuid()));
    $collection->userId = ID::fromString($view->getUserId());
    $collection->name = CollectionName::fromStored($view->getName());
    $collection->description = CollectionDescription::fromStored($view->getDescription());
    $collection->createdAt = $view->getCreatedAt();
    $collection->updatedAt = $view->getUpdatedAt();
    $collection->items = CollectionItemSet::create($items);

    return $collection;
  }

  public static function itemsLockKey(ID $id): string {
    return 'collection-items-' . $id->toString();
  }

  public function update(CollectionName $name, CollectionDescription $description): void {
    $this->recordThat(new CollectionWasUpdated(
      $this->aggregateRootId(),
      $name,
      $description,
      DateTime::now(),
    ));
  }

  public function delete(): void {
    $this->recordThat(new CollectionWasDeleted(
      $this->aggregateRootId(),
      DateTime::now(),
    ));
  }

  public function addItem(ID $itemId, ItemType $itemType): void {
    if ($this->items->contains($itemId)) {
      return;
    }

    $position = $this->items->getNextPosition();
    $item = CollectionItem::create($itemId, $itemType, $position);

    $this->recordThat(new ItemWasAddedToCollection(
      $this->aggregateRootId(),
      $item,
    ));
  }

  public function removeItem(ID $itemId): void {
    if (!$this->items->contains($itemId)) {
      return;
    }

    $this->recordThat(new ItemWasRemovedFromCollection(
      $this->aggregateRootId(),
      $itemId,
    ));
  }

  /**
   * @param array<string> $orderedItemIds
   */
  public function reorderItems(array $orderedItemIds): void {
    $this->recordThat(new CollectionItemsWereReordered(
      $this->aggregateRootId(),
      $orderedItemIds,
    ));
  }

  protected function applyCollectionWasCreated(CollectionWasCreated $event): void {
    $this->userId = $event->userId;
    $this->name = $event->name;
    $this->description = $event->description;
    $this->createdAt = $event->createdAt;
  }

  protected function applyCollectionWasUpdated(CollectionWasUpdated $event): void {
    $this->name = $event->name;
    $this->description = $event->description;
    $this->updatedAt = $event->updatedAt;
  }

  protected function applyItemWasAddedToCollection(ItemWasAddedToCollection $event): void {
    $this->items = $this->items->add($event->item);
  }

  protected function applyItemWasRemovedFromCollection(ItemWasRemovedFromCollection $event): void {
    $this->items = $this->items->remove($event->itemId);
  }

  protected function applyCollectionItemsWereReordered(CollectionItemsWereReordered $event): void {
    $this->items = $this->items->reorder($event->orderedItemIds);
  }

  public function getUserId(): ID {
    return $this->userId;
  }

  public function getName(): CollectionName {
    return $this->name;
  }

  public function getDescription(): CollectionDescription {
    return $this->description;
  }

  public function getItems(): CollectionItemSet {
    return $this->items;
  }

  public function getItemCount(): int {
    return $this->items->count();
  }

  public function getCreatedAt(): DateTime {
    return $this->createdAt;
  }

  public function getUpdatedAt(): ?DateTime {
    return $this->updatedAt;
  }

  public function isOwnedBy(?ID $userId): bool {
    return $this->userId->equals($userId);
  }
}
