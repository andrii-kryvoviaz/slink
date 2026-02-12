<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Tag\Domain\Event;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Event\TagWasDeleted;

final class TagWasDeletedTest extends TestCase {

  #[Test]
  public function itCreatesEventWithIdAndUserId(): void {
    $id = ID::generate();
    $event = new TagWasDeleted($id, 'user-123');

    $this->assertEquals($id, $event->id);
    $this->assertEquals('user-123', $event->userId);
  }

  #[Test]
  public function itCreatesEventWithDirectChildIds(): void {
    $id = ID::generate();
    $event = new TagWasDeleted($id, 'user-123', ['child-1', 'child-2']);

    $this->assertEquals(['child-1', 'child-2'], $event->directChildIds);
  }

  #[Test]
  public function itDefaultsToEmptyChildIds(): void {
    $id = ID::generate();
    $event = new TagWasDeleted($id, 'user-123');

    $this->assertEquals([], $event->directChildIds);
  }

  #[Test]
  public function itSerializesToPayload(): void {
    $id = ID::generate();
    $event = new TagWasDeleted($id, 'user-123', ['child-1', 'child-2']);
    $payload = $event->toPayload();

    $this->assertArrayHasKey('uuid', $payload);
    $this->assertArrayHasKey('userId', $payload);
    $this->assertArrayHasKey('directChildIds', $payload);
    $this->assertEquals($id->toString(), $payload['uuid']);
    $this->assertEquals('user-123', $payload['userId']);
    $this->assertEquals(['child-1', 'child-2'], $payload['directChildIds']);
  }

  #[Test]
  public function itDeserializesFromPayload(): void {
    $id = ID::generate();
    $payload = [
      'uuid' => $id->toString(),
      'userId' => 'user-456',
      'directChildIds' => ['child-1', 'child-2'],
    ];

    $event = TagWasDeleted::fromPayload($payload);

    $this->assertEquals($id->toString(), $event->id->toString());
    $this->assertEquals('user-456', $event->userId);
    $this->assertEquals(['child-1', 'child-2'], $event->directChildIds);
  }

  #[Test]
  public function itDeserializesFromPayloadWithoutUserId(): void {
    $id = ID::generate();
    $payload = ['uuid' => $id->toString()];

    $event = TagWasDeleted::fromPayload($payload);

    $this->assertEquals('', $event->userId);
  }

  #[Test]
  public function itDeserializesFromPayloadWithoutDirectChildIds(): void {
    $id = ID::generate();
    $payload = [
      'uuid' => $id->toString(),
      'userId' => 'user-789',
    ];

    $event = TagWasDeleted::fromPayload($payload);

    $this->assertEquals([], $event->directChildIds);
  }

  #[Test]
  public function itRoundTripsCorrectly(): void {
    $id = ID::generate();
    $originalEvent = new TagWasDeleted($id, 'user-123', ['child-1', 'child-2']);

    $payload = $originalEvent->toPayload();
    $recreatedEvent = TagWasDeleted::fromPayload($payload);

    $this->assertEquals($originalEvent->id->toString(), $recreatedEvent->id->toString());
    $this->assertEquals($originalEvent->userId, $recreatedEvent->userId);
    $this->assertEquals($originalEvent->directChildIds, $recreatedEvent->directChildIds);
  }
}
