<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Domain\Event;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\Event\ItemWasRemovedFromCollection;
use Slink\Shared\Domain\ValueObject\ID;

final class ItemWasRemovedFromCollectionTest extends TestCase {
  #[Test]
  public function itCreatesEvent(): void {
    $collectionId = ID::generate();
    $itemId = ID::generate();
    
    $event = new ItemWasRemovedFromCollection($collectionId, $itemId);
    
    $this->assertTrue($event->collectionId->equals($collectionId));
    $this->assertTrue($event->itemId->equals($itemId));
  }

  #[Test]
  public function itSerializesToPayload(): void {
    $collectionId = ID::generate();
    $itemId = ID::generate();
    
    $event = new ItemWasRemovedFromCollection($collectionId, $itemId);
    $payload = $event->toPayload();
    
    $this->assertArrayHasKey('collectionId', $payload);
    $this->assertArrayHasKey('itemId', $payload);
    $this->assertEquals($collectionId->toString(), $payload['collectionId']);
    $this->assertEquals($itemId->toString(), $payload['itemId']);
  }

  #[Test]
  public function itDeserializesFromPayload(): void {
    $collectionId = ID::generate();
    $itemId = ID::generate();
    
    $payload = [
      'collectionId' => $collectionId->toString(),
      'itemId' => $itemId->toString(),
    ];
    
    $event = ItemWasRemovedFromCollection::fromPayload($payload);
    
    $this->assertTrue($event->collectionId->equals($collectionId));
    $this->assertTrue($event->itemId->equals($itemId));
  }
}
