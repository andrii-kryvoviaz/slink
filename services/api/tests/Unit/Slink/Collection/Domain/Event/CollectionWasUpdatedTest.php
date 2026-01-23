<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Domain\Event;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\Event\CollectionWasUpdated;
use Slink\Collection\Domain\ValueObject\CollectionDescription;
use Slink\Collection\Domain\ValueObject\CollectionName;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final class CollectionWasUpdatedTest extends TestCase {
  #[Test]
  public function itCreatesEvent(): void {
    $id = ID::generate();
    $name = CollectionName::fromString('Updated Collection');
    $description = CollectionDescription::fromString('Updated description');
    $updatedAt = DateTime::now();
    
    $event = new CollectionWasUpdated($id, $name, $description, $updatedAt);
    
    $this->assertTrue($event->id->equals($id));
    $this->assertEquals($name->toString(), $event->name->toString());
    $this->assertEquals($description->toString(), $event->description->toString());
    $this->assertEquals($updatedAt->toString(), $event->updatedAt->toString());
  }

  #[Test]
  public function itSerializesToPayload(): void {
    $id = ID::generate();
    $name = CollectionName::fromString('Updated Collection');
    $description = CollectionDescription::fromString('Updated description');
    $updatedAt = DateTime::now();
    
    $event = new CollectionWasUpdated($id, $name, $description, $updatedAt);
    $payload = $event->toPayload();
    
    $this->assertArrayHasKey('id', $payload);
    $this->assertArrayHasKey('name', $payload);
    $this->assertArrayHasKey('description', $payload);
    $this->assertArrayHasKey('updatedAt', $payload);
    $this->assertEquals($id->toString(), $payload['id']);
  }

  #[Test]
  public function itDeserializesFromPayload(): void {
    $id = ID::generate();
    
    $payload = [
      'id' => $id->toString(),
      'name' => 'Updated Collection',
      'description' => 'Updated description',
      'updatedAt' => '2024-01-01T00:00:00+00:00',
    ];
    
    $event = CollectionWasUpdated::fromPayload($payload);
    
    $this->assertTrue($event->id->equals($id));
    $this->assertEquals('Updated Collection', $event->name->toString());
    $this->assertEquals('Updated description', $event->description->toString());
  }
}
