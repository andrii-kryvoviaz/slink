<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Tag\Domain\Event;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Event\TagWasMoved;
use Slink\Tag\Domain\ValueObject\TagPath;

final class TagWasMovedTest extends TestCase {

  #[Test]
  public function itCreatesEventWithAllProperties(): void {
    $id = ID::generate();
    $newParentId = ID::generate();
    $updatedAt = DateTime::now();
    $newPath = TagPath::fromString('#parent/tag');

    $event = new TagWasMoved($id, $newParentId, $newPath, $updatedAt);

    $this->assertEquals($id, $event->id);
    $this->assertEquals($newParentId, $event->newParentId);
    $this->assertEquals($updatedAt, $event->updatedAt);
    $this->assertEquals($newPath, $event->newPath);
  }

  #[Test]
  public function itCreatesEventWithoutOptionalProperties(): void {
    $id = ID::generate();

    $event = new TagWasMoved($id);

    $this->assertEquals($id, $event->id);
    $this->assertNull($event->newParentId);
    $this->assertNull($event->updatedAt);
    $this->assertNull($event->newPath);
  }

  #[Test]
  public function itSerializesToPayload(): void {
    $id = ID::generate();
    $newParentId = ID::generate();
    $updatedAt = DateTime::now();
    $newPath = TagPath::fromString('#new/path');

    $event = new TagWasMoved($id, $newParentId, $newPath, $updatedAt);
    $payload = $event->toPayload();

    $this->assertArrayHasKey('uuid', $payload);
    $this->assertArrayHasKey('new_path', $payload);
    $this->assertArrayHasKey('new_parent_id', $payload);
    $this->assertArrayHasKey('updated_at', $payload);
    $this->assertEquals($id->toString(), $payload['uuid']);
    $this->assertEquals($newParentId->toString(), $payload['new_parent_id']);
    $this->assertEquals($newPath->getValue(), $payload['new_path']);
    $this->assertEquals($updatedAt->toString(), $payload['updated_at']);
  }

  #[Test]
  public function itSerializesNullParentToPayload(): void {
    $id = ID::generate();

    $event = new TagWasMoved($id);
    $payload = $event->toPayload();

    $this->assertNull($payload['new_parent_id']);
    $this->assertNull($payload['new_path']);
  }

  #[Test]
  public function itDeserializesFromPayload(): void {
    $id = ID::generate();
    $newParentId = ID::generate();
    $updatedAt = DateTime::now();

    $payload = [
      'uuid' => $id->toString(),
      'new_parent_id' => $newParentId->toString(),
      'new_path' => '#parent/child',
      'updated_at' => $updatedAt->toString(),
    ];

    $event = TagWasMoved::fromPayload($payload);

    $this->assertEquals($id->toString(), $event->id->toString());
    $this->assertNotNull($event->newParentId);
    $this->assertNotNull($event->updatedAt);
    $this->assertNotNull($event->newPath);
    $this->assertEquals($newParentId->toString(), $event->newParentId->toString());
    $this->assertEquals('#parent/child', $event->newPath->getValue());
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
    $this->assertNull($event->newPath);
  }

  #[Test]
  public function itRoundTripsCorrectly(): void {
    $id = ID::generate();
    $newParentId = ID::generate();
    $updatedAt = DateTime::now();
    $newPath = TagPath::fromString('#new-parent/tag');

    $originalEvent = new TagWasMoved($id, $newParentId, $newPath, $updatedAt);

    $payload = $originalEvent->toPayload();
    $recreatedEvent = TagWasMoved::fromPayload($payload);

    $this->assertEquals($originalEvent->id->toString(), $recreatedEvent->id->toString());
    $this->assertNotNull($originalEvent->newParentId);
    $this->assertNotNull($recreatedEvent->newParentId);
    $this->assertNotNull($originalEvent->updatedAt);
    $this->assertNotNull($recreatedEvent->updatedAt);
    $this->assertNotNull($originalEvent->newPath);
    $this->assertNotNull($recreatedEvent->newPath);
    $this->assertEquals($originalEvent->newParentId->toString(), $recreatedEvent->newParentId->toString());
    $this->assertEquals($originalEvent->newPath->getValue(), $recreatedEvent->newPath->getValue());
    $this->assertEquals($originalEvent->updatedAt->toString(), $recreatedEvent->updatedAt->toString());
  }
}
