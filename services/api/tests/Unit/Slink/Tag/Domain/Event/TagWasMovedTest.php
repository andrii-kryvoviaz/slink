<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Tag\Domain\Event;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Event\TagWasMoved;

final class TagWasMovedTest extends TestCase {

  #[Test]
  public function itCreatesEventWithAllProperties(): void {
    $id = ID::generate();
    $newParentId = ID::generate();
    $updatedAt = DateTime::now();

    $event = new TagWasMoved($id, $newParentId, $updatedAt);

    $this->assertEquals($id, $event->id);
    $this->assertEquals($newParentId, $event->newParentId);
    $this->assertEquals($updatedAt, $event->updatedAt);
  }

  #[Test]
  public function itCreatesEventWithoutOptionalProperties(): void {
    $id = ID::generate();

    $event = new TagWasMoved($id);

    $this->assertEquals($id, $event->id);
    $this->assertNull($event->newParentId);
    $this->assertNull($event->updatedAt);
  }

  #[Test]
  public function itSerializesToPayload(): void {
    $id = ID::generate();
    $newParentId = ID::generate();
    $updatedAt = DateTime::now();

    $event = new TagWasMoved($id, $newParentId, $updatedAt);
    $payload = $event->toPayload();

    $this->assertArrayHasKey('uuid', $payload);
    $this->assertArrayNotHasKey('new_path', $payload);
    $this->assertArrayHasKey('new_parent_id', $payload);
    $this->assertArrayHasKey('updated_at', $payload);
    $this->assertEquals($id->toString(), $payload['uuid']);
    $this->assertEquals($newParentId->toString(), $payload['new_parent_id']);
    $this->assertEquals($updatedAt->toString(), $payload['updated_at']);
  }

  #[Test]
  public function itSerializesNullParentToPayload(): void {
    $id = ID::generate();

    $event = new TagWasMoved($id);
    $payload = $event->toPayload();

    $this->assertNull($payload['new_parent_id']);
  }

  #[Test]
  public function itDeserializesFromPayload(): void {
    $id = ID::generate();
    $newParentId = ID::generate();
    $updatedAt = DateTime::now();

    $payload = [
      'uuid' => $id->toString(),
      'new_parent_id' => $newParentId->toString(),
      'updated_at' => $updatedAt->toString(),
    ];

    $event = TagWasMoved::fromPayload($payload);

    $this->assertEquals($id->toString(), $event->id->toString());
    $this->assertNotNull($event->newParentId);
    $this->assertNotNull($event->updatedAt);
    $this->assertEquals($newParentId->toString(), $event->newParentId->toString());
    $this->assertEquals($updatedAt->toString(), $event->updatedAt->toString());
  }

  #[Test]
  public function itDeserializesFromPayloadWithMissingOptionalFields(): void {
    $id = ID::generate();

    $payload = [
      'uuid' => $id->toString(),
    ];

    $event = TagWasMoved::fromPayload($payload);

    $this->assertEquals($id->toString(), $event->id->toString());
    $this->assertNull($event->newParentId);
    $this->assertNull($event->updatedAt);
  }

  #[Test]
  public function itRoundTripsCorrectly(): void {
    $id = ID::generate();
    $newParentId = ID::generate();
    $updatedAt = DateTime::now();

    $originalEvent = new TagWasMoved($id, $newParentId, $updatedAt);

    $payload = $originalEvent->toPayload();
    $recreatedEvent = TagWasMoved::fromPayload($payload);

    $this->assertEquals($originalEvent->id->toString(), $recreatedEvent->id->toString());
    $this->assertNotNull($originalEvent->newParentId);
    $this->assertNotNull($recreatedEvent->newParentId);
    $this->assertNotNull($originalEvent->updatedAt);
    $this->assertNotNull($recreatedEvent->updatedAt);
    $this->assertEquals($originalEvent->newParentId->toString(), $recreatedEvent->newParentId->toString());
    $this->assertEquals($originalEvent->updatedAt->toString(), $recreatedEvent->updatedAt->toString());
  }
}
