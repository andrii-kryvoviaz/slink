<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Tag\Domain\Event;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Event\TagWasDeleted;

final class TagWasDeletedTest extends TestCase {

  #[Test]
  public function itCreatesEventWithId(): void {
    $id = ID::generate();
    $event = new TagWasDeleted($id);

    $this->assertEquals($id, $event->id);
  }

  #[Test]
  public function itSerializesToPayload(): void {
    $id = ID::generate();
    $event = new TagWasDeleted($id);
    $payload = $event->toPayload();

    $this->assertArrayHasKey('uuid', $payload);
    $this->assertEquals($id->toString(), $payload['uuid']);
  }

  #[Test]
  public function itDeserializesFromPayload(): void {
    $id = ID::generate();
    $payload = ['uuid' => $id->toString()];

    $event = TagWasDeleted::fromPayload($payload);

    $this->assertEquals($id->toString(), $event->id->toString());
  }

  #[Test]
  public function itHasCorrectPayloadStructure(): void {
    $id = ID::generate();
    $event = new TagWasDeleted($id);
    $payload = $event->toPayload();

    $this->assertCount(1, $payload);
    $this->assertArrayHasKey('uuid', $payload);
  }

  #[Test]
  public function itRoundTripsCorrectly(): void {
    $originalId = ID::generate();
    $originalEvent = new TagWasDeleted($originalId);
    
    $payload = $originalEvent->toPayload();
    $recreatedEvent = TagWasDeleted::fromPayload($payload);

    $this->assertEquals($originalEvent->id->toString(), $recreatedEvent->id->toString());
  }

  #[Test]
  public function itHasPublicIdProperty(): void {
    $id = ID::generate();
    $event = new TagWasDeleted($id);

    $this->assertEquals($id, $event->id);
  }
}