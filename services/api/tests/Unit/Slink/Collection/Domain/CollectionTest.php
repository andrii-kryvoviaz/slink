<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Domain;

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
use Slink\Shared\Domain\ValueObject\ID;

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
  public function itTracksDeletedState(): void {
    $collection = $this->createCollection();
    
    $this->assertFalse($collection->isDeleted());
    
    $collection->delete();
    $collection->releaseEvents();
    
    $this->assertTrue($collection->isDeleted());
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
  public function itCreatesSnapshot(): void {
    $collection = $this->createCollection();
    $collection->addItem(ID::generate(), ItemType::Image);
    
    $snapshot = $collection->createSnapshot();
    
    $this->assertEquals(2, $snapshot->aggregateRootVersion());
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
