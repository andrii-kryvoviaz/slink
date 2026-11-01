<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Domain;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\Collection;
use Slink\Collection\Domain\Enum\ItemType;
use Slink\Collection\Domain\Event\CollectionItemsWereReordered;
use Slink\Collection\Domain\Event\CollectionWasCreated;
use Slink\Collection\Domain\Event\CollectionWasDeleted;
use Slink\Collection\Domain\Event\CollectionWasUpdated;
use Slink\Collection\Domain\Event\ItemWasAddedToCollection;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Collection\Domain\Event\ItemWasRemovedFromCollection;
use Slink\Collection\Domain\ValueObject\CollectionDescription;
use Slink\Collection\Domain\ValueObject\CollectionItem;
use Slink\Collection\Domain\ValueObject\CollectionName;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionView;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class CollectionTest extends TestCase {
  #[Test]
  public function itCreatesCollection(): void {
    $id = ID::generate();
    $userId = ID::generate();
    $name = CollectionName::fromString('My Collection');
    $description = CollectionDescription::fromString('Test description');

    $collection = Collection::create($id, $userId, $name, $description);

    $events = $collection->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(CollectionWasCreated::class, $events[0]);
    
    $event = $events[0];
    $this->assertTrue($event->id->equals($id));
    $this->assertTrue($event->userId->equals($userId));
    $this->assertEquals($name->toString(), $event->name->toString());
    $this->assertEquals($description->toString(), $event->description->toString());
  }

  #[Test]
  public function itUpdatesCollection(): void {
    $collection = $this->createCollection();
    
    $newName = CollectionName::fromString('Updated Name');
    $newDescription = CollectionDescription::fromString('Updated description');
    
    $collection->update($newName, $newDescription);
    
    $events = $collection->releaseEvents();
    $this->assertCount(2, $events);
    $this->assertInstanceOf(CollectionWasUpdated::class, $events[1]);
    
    $event = $events[1];
    $this->assertEquals($newName->toString(), $event->name->toString());
    $this->assertEquals($newDescription->toString(), $event->description->toString());
  }

  #[Test]
  public function itDeletesCollection(): void {
    $collection = $this->createCollection();
    
    $collection->delete();
    
    $events = $collection->releaseEvents();
    $this->assertCount(2, $events);
    $this->assertInstanceOf(CollectionWasDeleted::class, $events[1]);
  }

  #[Test]
  public function itAddsItemToCollection(): void {
    $collection = $this->createCollection();
    $itemId = ID::generate();
    
    $collection->addItem($itemId, ItemType::Image);
    
    $events = $collection->releaseEvents();
    $this->assertCount(2, $events);
    $this->assertInstanceOf(ItemWasAddedToCollection::class, $events[1]);
    
    $event = $events[1];
    $this->assertTrue($event->item->getItemId()->equals($itemId));
    $this->assertEquals(ItemType::Image, $event->item->getItemType());
    $this->assertEquals(1.0, $event->item->getPosition());
  }

  #[Test]
  public function itDoesNotAddDuplicateItem(): void {
    $collection = $this->createCollection();
    $itemId = ID::generate();
    
    $collection->addItem($itemId, ItemType::Image);
    $collection->addItem($itemId, ItemType::Image);
    
    $events = $collection->releaseEvents();
    $this->assertCount(2, $events);
  }

  #[Test]
  public function itAssignsSequentialPositionsToItems(): void {
    $collection = $this->createCollection();
    $item1Id = ID::generate();
    $item2Id = ID::generate();
    $item3Id = ID::generate();
    
    $collection->addItem($item1Id, ItemType::Image);
    $collection->addItem($item2Id, ItemType::Image);
    $collection->addItem($item3Id, ItemType::Image);
    
    $events = $collection->releaseEvents();
    $this->assertInstanceOf(ItemWasAddedToCollection::class, $events[1]);
    $this->assertInstanceOf(ItemWasAddedToCollection::class, $events[2]);
    $this->assertInstanceOf(ItemWasAddedToCollection::class, $events[3]);
  }

  #[Test]
  public function itRemovesItemFromCollection(): void {
    $collection = $this->createCollection();
    $itemId = ID::generate();
    
    $collection->addItem($itemId, ItemType::Image);
    $collection->removeItem($itemId);
    
    $events = $collection->releaseEvents();
    $this->assertCount(3, $events);
    $this->assertInstanceOf(ItemWasRemovedFromCollection::class, $events[2]);
    
    $event = $events[2];
    $this->assertTrue($event->itemId->equals($itemId));
  }

  #[Test]
  public function itDoesNotRemoveNonExistentItem(): void {
    $collection = $this->createCollection();
    $itemId = ID::generate();
    
    $collection->removeItem($itemId);
    
    $events = $collection->releaseEvents();
    $this->assertCount(1, $events);
  }

  #[Test]
  public function itReordersItems(): void {
    $collection = $this->createCollection();
    $item1Id = ID::generate();
    $item2Id = ID::generate();
    $item3Id = ID::generate();
    
    $collection->addItem($item1Id, ItemType::Image);
    $collection->addItem($item2Id, ItemType::Image);
    $collection->addItem($item3Id, ItemType::Image);
    
    $newOrder = [$item3Id->toString(), $item1Id->toString(), $item2Id->toString()];
    $collection->reorderItems($newOrder);
    
    $events = $collection->releaseEvents();
    $this->assertCount(5, $events);
    $this->assertInstanceOf(CollectionItemsWereReordered::class, $events[4]);
    
    $event = $events[4];
    $this->assertEquals($newOrder, $event->orderedItemIds);
  }

  #[Test]
  public function itChecksOwnership(): void {
    $userId = ID::generate();
    $collection = Collection::create(
      ID::generate(),
      $userId,
      CollectionName::fromString('Test'),
      CollectionDescription::fromString('Test')
    );
    
    $collection->releaseEvents();
    
    $this->assertTrue($collection->isOwnedBy($userId));
    $this->assertFalse($collection->isOwnedBy(ID::generate()));
  }

  #[Test]
  public function itCountsItems(): void {
    $collection = $this->createCollection();
    
    $this->assertEquals(0, $collection->getItemCount());
    
    $collection->addItem(ID::generate(), ItemType::Image);
    $collection->releaseEvents();
    
    $this->assertEquals(1, $collection->getItemCount());
    
    $collection->addItem(ID::generate(), ItemType::Image);
    $collection->releaseEvents();
    
    $this->assertEquals(2, $collection->getItemCount());
  }

  #[Test]
  public function itGetsCollectionDetails(): void {
    $id = ID::generate();
    $userId = ID::generate();
    $name = CollectionName::fromString('My Collection');
    $description = CollectionDescription::fromString('Test description');
    
    $collection = Collection::create($id, $userId, $name, $description);
    $collection->releaseEvents();
    
    $this->assertTrue($collection->aggregateRootId()->equals($id));
    $this->assertTrue($collection->getUserId()->equals($userId));
    $this->assertEquals($name->toString(), $collection->getName()->toString());
    $this->assertEquals($description->toString(), $collection->getDescription()->toString());
    $this->assertInstanceOf(DateTime::class, $collection->getCreatedAt());
    $this->assertNull($collection->getUpdatedAt());
  }

  #[Test]
  public function itTracksUpdatedAt(): void {
    $collection = $this->createCollection();
    
    $this->assertNull($collection->getUpdatedAt());
    
    $collection->update(
      CollectionName::fromString('Updated'),
      CollectionDescription::fromString('Updated')
    );
    $collection->releaseEvents();
    
    $this->assertNotNull($collection->getUpdatedAt());
  }

  #[Test]
  #[Group('sc-1178')]
  public function itHydratesEveryFieldFromItsViews(): void {
    $id = ID::generate();
    $ownerId = ID::generate();
    $createdAt = DateTime::fromString('2021-02-03 04:05:06');
    $updatedAt = DateTime::fromString('2022-03-04 05:06:07');
    $view = $this->collectionView($id, $ownerId, 'Stored name', 'Stored description', $createdAt, $updatedAt);
    $imageId = ID::generate();
    $addedAt = DateTime::fromString('2023-04-05 06:07:08');

    $collection = Collection::fromView($view, [$this->item($imageId, 7.5, $addedAt)]);

    $this->assertTrue($id->equals($collection->aggregateRootId()));
    $this->assertSame($ownerId->toString(), $collection->getUserId()->toString());
    $this->assertSame('Stored name', $collection->getName()->toString());
    $this->assertSame('Stored description', $collection->getDescription()->toString());
    $this->assertTrue($createdAt->equals($collection->getCreatedAt()));
    $this->assertTrue($updatedAt->equals($collection->getUpdatedAt()));
    $this->assertSame(1, $collection->getItemCount());
    $item = $collection->getItems()->get($imageId);
    $this->assertInstanceOf(CollectionItem::class, $item);
    $this->assertSame(ItemType::Image, $item->getItemType());
    $this->assertSame(7.5, $item->getPosition());
    $this->assertTrue($addedAt->equals($item->getAddedAt()));
  }

  #[Test]
  #[Group('sc-1178')]
  public function itHydratesACollectionWithoutDescriptionItemsOrUpdates(): void {
    $view = $this->collectionView(description: '');

    $collection = Collection::fromView($view, []);

    $this->assertSame('', $collection->getDescription()->toString());
    $this->assertNull($collection->getUpdatedAt());
    $this->assertSame(0, $collection->getItemCount());
  }

  #[Test]
  #[Group('sc-1178')]
  public function itRecordsNoEventsWhenHydrated(): void {
    $view = $this->collectionView();

    $collection = Collection::fromView($view, [$this->item(ID::generate(), 1.0)]);

    $this->assertSame([], $collection->releaseEvents());
  }

  #[Test]
  #[Group('sc-1178')]
  public function itAddsAfterTheHighestHydratedPosition(): void {
    $view = $this->collectionView();
    $collection = Collection::fromView($view, [
      $this->item(ID::generate(), 1.0),
      $this->item(ID::generate(), 4.0),
    ]);

    $collection->addItem(ID::generate(), ItemType::Image);

    $events = $collection->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(ItemWasAddedToCollection::class, $events[0]);
    $this->assertSame(5.0, $events[0]->item->getPosition());
  }

  #[Test]
  #[Group('sc-1178')]
  public function itDoesNotAddAnItemAlreadyHydrated(): void {
    $view = $this->collectionView();
    $imageId = ID::generate();
    $collection = Collection::fromView($view, [$this->item($imageId, 1.0)]);

    $collection->addItem($imageId, ItemType::Image);

    $this->assertSame([], $collection->releaseEvents());
  }

  #[Test]
  #[Group('sc-1178')]
  public function itRemovesAHydratedItem(): void {
    $view = $this->collectionView();
    $imageId = ID::generate();
    $remainingId = ID::generate();
    $collection = Collection::fromView($view, [
      $this->item($imageId, 1.0),
      $this->item($remainingId, 2.0),
    ]);

    $collection->removeItem($imageId);

    $this->assertFalse($collection->getItems()->contains($imageId));
    $this->assertTrue($collection->getItems()->contains($remainingId));
    $events = $collection->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(ItemWasRemovedFromCollection::class, $events[0]);
  }

  #[Test]
  #[Group('sc-1178')]
  public function itReordersHydratedItems(): void {
    $view = $this->collectionView();
    $firstId = ID::generate();
    $secondId = ID::generate();
    $collection = Collection::fromView($view, [
      $this->item($firstId, 1.0),
      $this->item($secondId, 2.0),
    ]);
    $newOrder = [$secondId->toString(), $firstId->toString()];

    $collection->reorderItems($newOrder);

    $events = $collection->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(CollectionItemsWereReordered::class, $events[0]);
    $this->assertSame($newOrder, $events[0]->orderedItemIds);
  }

  #[Test]
  #[Group('sc-1178')]
  public function itDeletesAHydratedCollection(): void {
    $id = ID::generate();
    $collection = Collection::fromView($this->collectionView($id), []);

    $collection->delete();

    $events = $collection->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(CollectionWasDeleted::class, $events[0]);
    $this->assertSame($id->toString(), $events[0]->id->toString());
  }

  #[Test]
  #[Group('sc-1178')]
  public function itChecksOwnershipOfAHydratedCollection(): void {
    $ownerId = ID::generate();
    $collection = Collection::fromView($this->collectionView(ownerId: $ownerId), []);

    $this->assertTrue($collection->isOwnedBy($ownerId));
    $this->assertFalse($collection->isOwnedBy(ID::generate()));
  }

  private function collectionView(
    ?ID $id = null,
    ?ID $ownerId = null,
    string $name = 'Stored name',
    string $description = 'Stored description',
    ?DateTime $createdAt = null,
    ?DateTime $updatedAt = null,
  ): CollectionView {
    $owner = $this->createStub(UserView::class);
    $owner->method('getUuid')->willReturn(($ownerId ?? ID::generate())->toString());

    return new CollectionView(
      ($id ?? ID::generate())->toString(),
      $owner,
      $name,
      $description,
      $createdAt ?? DateTime::fromString('2021-02-03 04:05:06'),
      $updatedAt,
    );
  }

  private function item(ID $itemId, float $position, ?DateTime $addedAt = null): CollectionItem {
    return CollectionItem::create(
      $itemId,
      ItemType::Image,
      $position,
      $addedAt ?? DateTime::fromString('2023-04-05 06:07:08'),
    );
  }

  private function createCollection(): Collection {
    return Collection::create(
      ID::generate(),
      ID::generate(),
      CollectionName::fromString('Test Collection'),
      CollectionDescription::fromString('Test description')
    );
  }
}
