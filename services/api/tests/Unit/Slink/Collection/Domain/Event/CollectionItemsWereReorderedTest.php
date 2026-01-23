<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Domain\Event;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\Event\CollectionItemsWereReordered;
use Slink\Shared\Domain\ValueObject\ID;

final class CollectionItemsWereReorderedTest extends TestCase {
  #[Test]
  public function itCreatesEvent(): void {
    $collectionId = ID::generate();
    $orderedItemIds = [
      ID::generate()->toString(),
      ID::generate()->toString(),
      ID::generate()->toString(),
    ];
    
    $event = new CollectionItemsWereReordered($collectionId, $orderedItemIds);
    
    $this->assertTrue($event->collectionId->equals($collectionId));
    $this->assertEquals($orderedItemIds, $event->orderedItemIds);
  }

  #[Test]
  public function itSerializesToPayload(): void {
    $collectionId = ID::generate();
    $orderedItemIds = [
      ID::generate()->toString(),
      ID::generate()->toString(),
    ];
    
    $event = new CollectionItemsWereReordered($collectionId, $orderedItemIds);
    $payload = $event->toPayload();
    
    $this->assertArrayHasKey('collectionId', $payload);
    $this->assertArrayHasKey('orderedItemIds', $payload);
    $this->assertEquals($collectionId->toString(), $payload['collectionId']);
    $this->assertEquals($orderedItemIds, $payload['orderedItemIds']);
  }

  #[Test]
  public function itDeserializesFromPayload(): void {
    $collectionId = ID::generate();
    $orderedItemIds = [
      ID::generate()->toString(),
      ID::generate()->toString(),
    ];
    
    $payload = [
      'collectionId' => $collectionId->toString(),
      'orderedItemIds' => $orderedItemIds,
    ];
    
    $event = CollectionItemsWereReordered::fromPayload($payload);
    
    $this->assertTrue($event->collectionId->equals($collectionId));
    $this->assertEquals($orderedItemIds, $event->orderedItemIds);
  }

  #[Test]
  public function itHandlesEmptyOrderedList(): void {
    $collectionId = ID::generate();
    $orderedItemIds = [];
    
    $event = new CollectionItemsWereReordered($collectionId, $orderedItemIds);
    
    $this->assertEmpty($event->orderedItemIds);
  }
}
