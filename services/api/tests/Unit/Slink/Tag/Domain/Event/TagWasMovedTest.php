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
    $oldPath = TagPath::fromString('#old');
    $newPath = TagPath::fromString('#new');
    $oldParentId = ID::generate();
    $newParentId = ID::generate();
    $updatedAt = DateTime::now();

    $event = new TagWasMoved($id, $oldPath, $newPath, $oldParentId, $newParentId, $updatedAt);

    $this->assertEquals($id, $event->id);
    $this->assertEquals($oldPath, $event->oldPath);
    $this->assertEquals($newPath, $event->newPath);
    $this->assertEquals($oldParentId, $event->oldParentId);
    $this->assertEquals($newParentId, $event->newParentId);
    $this->assertEquals($updatedAt, $event->updatedAt);
  }

  #[Test]
  public function itCreatesEventWithoutOptionalProperties(): void {
    $id = ID::generate();
    $oldPath = TagPath::fromString('#old');
    $newPath = TagPath::fromString('#new');

    $event = new TagWasMoved($id, $oldPath, $newPath);

    $this->assertEquals($id, $event->id);
    $this->assertEquals($oldPath, $event->oldPath);
    $this->assertEquals($newPath, $event->newPath);
    $this->assertNull($event->oldParentId);
    $this->assertNull($event->newParentId);
    $this->assertNull($event->updatedAt);
  }

  #[Test]
  public function itSerializesToPayload(): void {
    $id = ID::generate();
    $oldPath = TagPath::fromString('#old');
    $newPath = TagPath::fromString('#new');
    $oldParentId = ID::generate();
    $newParentId = ID::generate();
    $updatedAt = DateTime::now();

    $event = new TagWasMoved($id, $oldPath, $newPath, $oldParentId, $newParentId, $updatedAt);
    $payload = $event->toPayload();

    $this->assertArrayHasKey('uuid', $payload);
    $this->assertArrayHasKey('old_path', $payload);
    $this->assertArrayHasKey('new_path', $payload);
    $this->assertArrayHasKey('old_parent_id', $payload);
    $this->assertArrayHasKey('new_parent_id', $payload);
    $this->assertArrayHasKey('updated_at', $payload);
    $this->assertEquals($id->toString(), $payload['uuid']);
    $this->assertEquals('#old', $payload['old_path']);
    $this->assertEquals('#new', $payload['new_path']);
    $this->assertEquals($oldParentId->toString(), $payload['old_parent_id']);
    $this->assertEquals($newParentId->toString(), $payload['new_parent_id']);
    $this->assertEquals($updatedAt->toString(), $payload['updated_at']);
  }

  #[Test]
  public function itSerializesNullParentsToPayload(): void {
    $id = ID::generate();
    $oldPath = TagPath::fromString('#old');
    $newPath = TagPath::fromString('#new');

    $event = new TagWasMoved($id, $oldPath, $newPath, null, null);
    $payload = $event->toPayload();

    $this->assertNull($payload['old_parent_id']);
    $this->assertNull($payload['new_parent_id']);
  }

  #[Test]
  public function itDeserializesFromPayload(): void {
    $id = ID::generate();
    $oldParentId = ID::generate();
    $newParentId = ID::generate();
    $updatedAt = DateTime::now();

    $payload = [
      'uuid' => $id->toString(),
      'old_path' => '#old',
      'new_path' => '#new',
      'old_parent_id' => $oldParentId->toString(),
      'new_parent_id' => $newParentId->toString(),
      'updated_at' => $updatedAt->toString(),
    ];

    $event = TagWasMoved::fromPayload($payload);

    $this->assertEquals($id->toString(), $event->id->toString());
    $this->assertEquals('#old', $event->oldPath->getValue());
    $this->assertEquals('#new', $event->newPath->getValue());
    $this->assertNotNull($event->oldParentId);
    $this->assertNotNull($event->newParentId);
    $this->assertNotNull($event->updatedAt);
    $this->assertEquals($oldParentId->toString(), $event->oldParentId->toString());
    $this->assertEquals($newParentId->toString(), $event->newParentId->toString());
    $this->assertEquals($updatedAt->toString(), $event->updatedAt->toString());
  }

  #[Test]
  public function itDeserializesFromPayloadWithMissingOptionalFields(): void {
    $id = ID::generate();

    $payload = [
      'uuid' => $id->toString(),
      'old_path' => '#old',
      'new_path' => '#new',
    ];

    $event = TagWasMoved::fromPayload($payload);

    $this->assertEquals($id->toString(), $event->id->toString());
    $this->assertEquals('#old', $event->oldPath->getValue());
    $this->assertEquals('#new', $event->newPath->getValue());
    $this->assertNull($event->oldParentId);
    $this->assertNull($event->newParentId);
    $this->assertNull($event->updatedAt);
  }

  #[Test]
  public function itRoundTripsCorrectly(): void {
    $id = ID::generate();
    $oldPath = TagPath::fromString('#old/child');
    $newPath = TagPath::fromString('#new/child');
    $oldParentId = ID::generate();
    $newParentId = ID::generate();
    $updatedAt = DateTime::now();

    $originalEvent = new TagWasMoved($id, $oldPath, $newPath, $oldParentId, $newParentId, $updatedAt);

    $payload = $originalEvent->toPayload();
    $recreatedEvent = TagWasMoved::fromPayload($payload);

    $this->assertEquals($originalEvent->id->toString(), $recreatedEvent->id->toString());
    $this->assertEquals($originalEvent->oldPath->getValue(), $recreatedEvent->oldPath->getValue());
    $this->assertEquals($originalEvent->newPath->getValue(), $recreatedEvent->newPath->getValue());
    $this->assertNotNull($originalEvent->oldParentId);
    $this->assertNotNull($recreatedEvent->oldParentId);
    $this->assertNotNull($originalEvent->newParentId);
    $this->assertNotNull($recreatedEvent->newParentId);
    $this->assertNotNull($originalEvent->updatedAt);
    $this->assertNotNull($recreatedEvent->updatedAt);
    $this->assertEquals($originalEvent->oldParentId->toString(), $recreatedEvent->oldParentId->toString());
    $this->assertEquals($originalEvent->newParentId->toString(), $recreatedEvent->newParentId->toString());
    $this->assertEquals($originalEvent->updatedAt->toString(), $recreatedEvent->updatedAt->toString());
  }
}
