<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Domain\Event;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\Event\CollectionWasCreated;
use Slink\Collection\Domain\ValueObject\CollectionDescription;
use Slink\Collection\Domain\ValueObject\CollectionName;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final class CollectionWasCreatedTest extends TestCase {
  #[Test]
  public function itCreatesEvent(): void {
    $id = ID::generate();
    $userId = ID::generate();
    $name = CollectionName::fromString('Test Collection');
    $description = CollectionDescription::fromString('Test description');
    $createdAt = DateTime::now();
    
    $event = new CollectionWasCreated($id, $userId, $name, $description, $createdAt);
    
    $this->assertTrue($event->id->equals($id));
    $this->assertTrue($event->userId->equals($userId));
    $this->assertEquals($name->toString(), $event->name->toString());
    $this->assertEquals($description->toString(), $event->description->toString());
    $this->assertEquals($createdAt->toString(), $event->createdAt->toString());
  }

  #[Test]
  public function itSerializesToPayload(): void {
    $id = ID::generate();
    $userId = ID::generate();
    $name = CollectionName::fromString('Test Collection');
    $description = CollectionDescription::fromString('Test description');
    $createdAt = DateTime::now();
    
    $event = new CollectionWasCreated($id, $userId, $name, $description, $createdAt);
    $payload = $event->toPayload();
    
    $this->assertArrayHasKey('uuid', $payload);
    $this->assertArrayHasKey('user', $payload);
    $this->assertArrayHasKey('name', $payload);
    $this->assertArrayHasKey('description', $payload);
    $this->assertArrayHasKey('createdAt', $payload);
    $this->assertEquals($id->toString(), $payload['uuid']);
    $this->assertEquals($userId->toString(), $payload['user']);
  }

  #[Test]
  public function itDeserializesFromPayload(): void {
    $id = ID::generate();
    $userId = ID::generate();
    
    $payload = [
      'uuid' => $id->toString(),
      'user' => $userId->toString(),
      'name' => 'Test Collection',
      'description' => 'Test description',
      'createdAt' => '2024-01-01T00:00:00+00:00',
    ];
    
    $event = CollectionWasCreated::fromPayload($payload);
    
    $this->assertTrue($event->id->equals($id));
    $this->assertTrue($event->userId->equals($userId));
    $this->assertEquals('Test Collection', $event->name->toString());
    $this->assertEquals('Test description', $event->description->toString());
  }
}
