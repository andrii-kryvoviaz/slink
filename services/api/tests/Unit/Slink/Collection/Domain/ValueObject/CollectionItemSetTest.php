<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Domain\ValueObject;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\Enum\ItemType;
use Slink\Collection\Domain\ValueObject\CollectionItem;
use Slink\Collection\Domain\ValueObject\CollectionItemSet;
use Slink\Shared\Domain\ValueObject\ID;

final class CollectionItemSetTest extends TestCase {
  #[Test]
  public function itCreatesEmptySet(): void {
    $set = CollectionItemSet::empty();
    
    $this->assertTrue($set->isEmpty());
    $this->assertEquals(0, $set->count());
  }

  #[Test]
  public function itCreatesSetWithItems(): void {
    $item1 = CollectionItem::create(ID::generate(), ItemType::Image, 1.0);
    $item2 = CollectionItem::create(ID::generate(), ItemType::Image, 2.0);
    
    $set = CollectionItemSet::create([$item1, $item2]);
    
    $this->assertFalse($set->isEmpty());
    $this->assertEquals(2, $set->count());
  }

  #[Test]
  public function itAddsItem(): void {
    $set = CollectionItemSet::empty();
    $item = CollectionItem::create(ID::generate(), ItemType::Image, 1.0);
    
    $newSet = $set->add($item);
    
    $this->assertEquals(0, $set->count());
    $this->assertEquals(1, $newSet->count());
  }

  #[Test]
  public function itRemovesItem(): void {
    $itemId = ID::generate();
    $item = CollectionItem::create($itemId, ItemType::Image, 1.0);
    $set = CollectionItemSet::create([$item]);
    
    $newSet = $set->remove($itemId);
    
    $this->assertEquals(1, $set->count());
    $this->assertEquals(0, $newSet->count());
  }

  #[Test]
  public function itChecksIfContainsItem(): void {
    $itemId = ID::generate();
    $item = CollectionItem::create($itemId, ItemType::Image, 1.0);
    $set = CollectionItemSet::create([$item]);
    
    $this->assertTrue($set->contains($itemId));
    $this->assertFalse($set->contains(ID::generate()));
  }

  #[Test]
  public function itGetsItemById(): void {
    $itemId = ID::generate();
    $item = CollectionItem::create($itemId, ItemType::Image, 1.0);
    $set = CollectionItemSet::create([$item]);
    
    $retrieved = $set->get($itemId);
    
    $this->assertNotNull($retrieved);
    $this->assertTrue($retrieved->getItemId()->equals($itemId));
  }

  #[Test]
  public function itReturnsNullForNonExistentItem(): void {
    $set = CollectionItemSet::empty();
    
    $retrieved = $set->get(ID::generate());
    
    $this->assertNull($retrieved);
  }

  #[Test]
  public function itGetsAllItems(): void {
    $item1 = CollectionItem::create(ID::generate(), ItemType::Image, 1.0);
    $item2 = CollectionItem::create(ID::generate(), ItemType::Image, 2.0);
    $set = CollectionItemSet::create([$item1, $item2]);
    
    $items = $set->getItems();
    
    $this->assertCount(2, $items);
  }

  #[Test]
  public function itGetsSortedItems(): void {
    $item1 = CollectionItem::create(ID::generate(), ItemType::Image, 3.0);
    $item2 = CollectionItem::create(ID::generate(), ItemType::Image, 1.0);
    $item3 = CollectionItem::create(ID::generate(), ItemType::Image, 2.0);
    $set = CollectionItemSet::create([$item1, $item2, $item3]);
    
    $sortedItems = $set->getSortedItems();
    
    $this->assertEquals(1.0, $sortedItems[0]->getPosition());
    $this->assertEquals(2.0, $sortedItems[1]->getPosition());
    $this->assertEquals(3.0, $sortedItems[2]->getPosition());
  }

  #[Test]
  public function itCalculatesNextPosition(): void {
    $set = CollectionItemSet::empty();
    
    $this->assertEquals(1.0, $set->getNextPosition());
    
    $item1 = CollectionItem::create(ID::generate(), ItemType::Image, 1.0);
    $set = $set->add($item1);
    
    $this->assertEquals(2.0, $set->getNextPosition());
    
    $item2 = CollectionItem::create(ID::generate(), ItemType::Image, 5.0);
    $set = $set->add($item2);
    
    $this->assertEquals(6.0, $set->getNextPosition());
  }

  #[Test]
  public function itReordersItems(): void {
    $item1Id = ID::generate();
    $item2Id = ID::generate();
    $item3Id = ID::generate();
    
    $item1 = CollectionItem::create($item1Id, ItemType::Image, 1.0);
    $item2 = CollectionItem::create($item2Id, ItemType::Image, 2.0);
    $item3 = CollectionItem::create($item3Id, ItemType::Image, 3.0);
    
    $set = CollectionItemSet::create([$item1, $item2, $item3]);
    
    $newOrder = [$item3Id->toString(), $item1Id->toString(), $item2Id->toString()];
    $reorderedSet = $set->reorder($newOrder);
    
    $sortedItems = $reorderedSet->getSortedItems();
    
    $this->assertTrue($sortedItems[0]->getItemId()->equals($item3Id));
    $this->assertEquals(1.0, $sortedItems[0]->getPosition());
    
    $this->assertTrue($sortedItems[1]->getItemId()->equals($item1Id));
    $this->assertEquals(2.0, $sortedItems[1]->getPosition());
    
    $this->assertTrue($sortedItems[2]->getItemId()->equals($item2Id));
    $this->assertEquals(3.0, $sortedItems[2]->getPosition());
  }

  #[Test]
  public function itIgnoresNonExistentItemsWhenReordering(): void {
    $item1Id = ID::generate();
    $item1 = CollectionItem::create($item1Id, ItemType::Image, 1.0);
    $set = CollectionItemSet::create([$item1]);
    
    $nonExistentId = ID::generate();
    $newOrder = [$nonExistentId->toString(), $item1Id->toString()];
    $reorderedSet = $set->reorder($newOrder);
    
    $this->assertEquals(1, $reorderedSet->count());
  }

  #[Test]
  public function itSerializesToPayload(): void {
    $item1 = CollectionItem::create(ID::generate(), ItemType::Image, 1.0);
    $item2 = CollectionItem::create(ID::generate(), ItemType::Image, 2.0);
    $set = CollectionItemSet::create([$item1, $item2]);
    
    $payload = $set->toPayload();
    
    $this->assertCount(2, $payload);
    $this->assertArrayHasKey('itemId', $payload[0]);
    $this->assertArrayHasKey('position', $payload[0]);
  }

  #[Test]
  public function itDeserializesFromPayload(): void {
    $item1Id = ID::generate();
    $item2Id = ID::generate();
    
    $payload = [
      [
        'itemId' => $item1Id->toString(),
        'itemType' => 'image',
        'position' => 1.0,
        'addedAt' => '2024-01-01T00:00:00+00:00',
      ],
      [
        'itemId' => $item2Id->toString(),
        'itemType' => 'image',
        'position' => 2.0,
        'addedAt' => '2024-01-01T00:00:00+00:00',
      ],
    ];
    
    $set = CollectionItemSet::fromPayload($payload);
    
    $this->assertEquals(2, $set->count());
    $this->assertTrue($set->contains($item1Id));
    $this->assertTrue($set->contains($item2Id));
  }
}
