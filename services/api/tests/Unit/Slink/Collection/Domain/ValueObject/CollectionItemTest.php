<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Domain\ValueObject;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\Enum\ItemType;
use Slink\Collection\Domain\ValueObject\CollectionItem;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final class CollectionItemTest extends TestCase {
  #[Test]
  public function itCreatesCollectionItem(): void {
    $itemId = ID::generate();
    $itemType = ItemType::Image;
    $position = 1.0;
    
    $item = CollectionItem::create($itemId, $itemType, $position);
    
    $this->assertTrue($item->getItemId()->equals($itemId));
    $this->assertEquals($itemType, $item->getItemType());
    $this->assertEquals($position, $item->getPosition());
    $this->assertInstanceOf(DateTime::class, $item->getAddedAt());
  }

  #[Test]
  public function itCreatesCollectionItemWithCustomDate(): void {
    $itemId = ID::generate();
    $itemType = ItemType::Image;
    $position = 1.0;
    $addedAt = DateTime::now();
    
    $item = CollectionItem::create($itemId, $itemType, $position, $addedAt);
    
    $this->assertEquals($addedAt->toString(), $item->getAddedAt()->toString());
  }

  #[Test]
  public function itCreatesForImage(): void {
    $imageId = ID::generate();
    $position = 2.0;
    
    $item = CollectionItem::forImage($imageId, $position);
    
    $this->assertTrue($item->getItemId()->equals($imageId));
    $this->assertEquals(ItemType::Image, $item->getItemType());
    $this->assertEquals($position, $item->getPosition());
  }

  #[Test]
  public function itUpdatesPosition(): void {
    $item = CollectionItem::create(ID::generate(), ItemType::Image, 1.0);
    
    $newItem = $item->withPosition(5.0);
    
    $this->assertEquals(1.0, $item->getPosition());
    $this->assertEquals(5.0, $newItem->getPosition());
    $this->assertTrue($item->getItemId()->equals($newItem->getItemId()));
  }

  #[Test]
  public function itSerializesToPayload(): void {
    $itemId = ID::generate();
    $itemType = ItemType::Image;
    $position = 3.0;
    $addedAt = DateTime::now();
    
    $item = CollectionItem::create($itemId, $itemType, $position, $addedAt);
    
    $payload = $item->toPayload();
    
    $this->assertArrayHasKey('itemId', $payload);
    $this->assertArrayHasKey('itemType', $payload);
    $this->assertArrayHasKey('position', $payload);
    $this->assertArrayHasKey('addedAt', $payload);
    $this->assertEquals($itemId->toString(), $payload['itemId']);
    $this->assertEquals($itemType->value, $payload['itemType']);
    $this->assertEquals($position, $payload['position']);
    $this->assertEquals($addedAt->toString(), $payload['addedAt']);
  }

  #[Test]
  public function itDeserializesFromPayload(): void {
    $itemId = ID::generate();
    $position = 2.5;
    $addedAt = DateTime::now();
    
    $payload = [
      'itemId' => $itemId->toString(),
      'itemType' => 'image',
      'position' => $position,
      'addedAt' => $addedAt->toString(),
    ];
    
    $item = CollectionItem::fromPayload($payload);
    
    $this->assertTrue($item->getItemId()->equals($itemId));
    $this->assertEquals(ItemType::Image, $item->getItemType());
    $this->assertEquals($position, $item->getPosition());
    $this->assertEquals($addedAt->toString(), $item->getAddedAt()->toString());
  }
}
