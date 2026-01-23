<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Domain\Event;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\Enum\ItemType;
use Slink\Collection\Domain\Event\ItemWasAddedToCollection;
use Slink\Collection\Domain\ValueObject\CollectionItem;
use Slink\Shared\Domain\ValueObject\ID;

final class ItemWasAddedToCollectionTest extends TestCase {
  #[Test]
  public function itCreatesEvent(): void {
    $collectionId = ID::generate();
    $item = CollectionItem::create(ID::generate(), ItemType::Image, 1.0);
    
    $event = new ItemWasAddedToCollection($collectionId, $item);
    
    $this->assertTrue($event->collectionId->equals($collectionId));
    $this->assertTrue($event->item->getItemId()->equals($item->getItemId()));
    $this->assertEquals($item->getItemType(), $event->item->getItemType());
  }

  #[Test]
  public function itSerializesToPayload(): void {
    $collectionId = ID::generate();
    $itemId = ID::generate();
    $item = CollectionItem::create($itemId, ItemType::Image, 1.0);
    
    $event = new ItemWasAddedToCollection($collectionId, $item);
    $payload = $event->toPayload();
    
    $this->assertArrayHasKey('collectionId', $payload);
    $this->assertArrayHasKey('item', $payload);
    $this->assertEquals($collectionId->toString(), $payload['collectionId']);
    $this->assertIsArray($payload['item']);
    $this->assertEquals($itemId->toString(), $payload['item']['itemId']);
  }

  #[Test]
  public function itDeserializesFromPayload(): void {
    $collectionId = ID::generate();
    $itemId = ID::generate();
    
    $payload = [
      'collectionId' => $collectionId->toString(),
      'item' => [
        'itemId' => $itemId->toString(),
        'itemType' => 'image',
        'position' => 1.0,
        'addedAt' => '2024-01-01T00:00:00+00:00',
      ],
    ];
    
    $event = ItemWasAddedToCollection::fromPayload($payload);
    
    $this->assertTrue($event->collectionId->equals($collectionId));
    $this->assertTrue($event->item->getItemId()->equals($itemId));
    $this->assertEquals(ItemType::Image, $event->item->getItemType());
  }
}
