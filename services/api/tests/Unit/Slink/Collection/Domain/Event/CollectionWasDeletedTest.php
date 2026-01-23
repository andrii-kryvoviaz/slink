<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Domain\Event;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\Event\CollectionWasDeleted;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final class CollectionWasDeletedTest extends TestCase {
  #[Test]
  public function itCreatesEvent(): void {
    $id = ID::generate();
    $deletedAt = DateTime::now();
    
    $event = new CollectionWasDeleted($id, $deletedAt);
    
    $this->assertTrue($event->id->equals($id));
    $this->assertEquals($deletedAt->toString(), $event->deletedAt->toString());
  }

  #[Test]
  public function itSerializesToPayload(): void {
    $id = ID::generate();
    $deletedAt = DateTime::now();
    
    $event = new CollectionWasDeleted($id, $deletedAt);
    $payload = $event->toPayload();
    
    $this->assertArrayHasKey('id', $payload);
    $this->assertArrayHasKey('deletedAt', $payload);
    $this->assertEquals($id->toString(), $payload['id']);
    $this->assertEquals($deletedAt->toString(), $payload['deletedAt']);
  }

  #[Test]
  public function itDeserializesFromPayload(): void {
    $id = ID::generate();
    
    $payload = [
      'id' => $id->toString(),
      'deletedAt' => '2024-01-01T00:00:00+00:00',
    ];
    
    $event = CollectionWasDeleted::fromPayload($payload);
    
    $this->assertTrue($event->id->equals($id));
  }
}
