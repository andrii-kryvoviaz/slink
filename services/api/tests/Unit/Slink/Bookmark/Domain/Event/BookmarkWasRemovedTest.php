<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Bookmark\Domain\Event;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Bookmark\Domain\Event\BookmarkWasRemoved;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final class BookmarkWasRemovedTest extends TestCase {
  #[Test]
  public function itCreatesEvent(): void {
    $id = ID::generate();
    $removedAt = DateTime::now();

    $event = new BookmarkWasRemoved($id, $removedAt);

    $this->assertEquals($id, $event->id);
    $this->assertEquals($removedAt, $event->removedAt);
  }

  #[Test]
  public function itSerializesToPayload(): void {
    $id = ID::generate();
    $removedAt = DateTime::now();

    $event = new BookmarkWasRemoved($id, $removedAt);
    $payload = $event->toPayload();

    $this->assertEquals($id->toString(), $payload['uuid']);
    $this->assertEquals($removedAt->toString(), $payload['removedAt']);
  }

  #[Test]
  public function itDeserializesFromPayload(): void {
    $id = ID::generate();
    $removedAt = DateTime::now();

    $payload = [
      'uuid' => $id->toString(),
      'removedAt' => $removedAt->toString(),
    ];

    $event = BookmarkWasRemoved::fromPayload($payload);

    $this->assertEquals($id->toString(), $event->id->toString());
    $this->assertEquals($removedAt->toString(), $event->removedAt->toString());
  }

  #[Test]
  public function itRoundTripsPayloadSerialization(): void {
    $original = new BookmarkWasRemoved(
      ID::generate(),
      DateTime::now(),
    );

    $restored = BookmarkWasRemoved::fromPayload($original->toPayload());

    $this->assertEquals($original->id->toString(), $restored->id->toString());
    $this->assertEquals($original->removedAt->toString(), $restored->removedAt->toString());
  }
}
