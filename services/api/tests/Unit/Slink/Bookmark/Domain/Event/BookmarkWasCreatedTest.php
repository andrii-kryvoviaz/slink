<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Bookmark\Domain\Event;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Bookmark\Domain\Event\BookmarkWasCreated;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final class BookmarkWasCreatedTest extends TestCase {
  #[Test]
  public function itCreatesEvent(): void {
    $id = ID::generate();
    $imageId = ID::generate();
    $userId = ID::generate();
    $createdAt = DateTime::now();

    $event = new BookmarkWasCreated($id, $imageId, $userId, $createdAt);

    $this->assertEquals($id, $event->id);
    $this->assertEquals($imageId, $event->imageId);
    $this->assertEquals($userId, $event->userId);
    $this->assertEquals($createdAt, $event->createdAt);
  }

  #[Test]
  public function itSerializesToPayload(): void {
    $id = ID::generate();
    $imageId = ID::generate();
    $userId = ID::generate();
    $createdAt = DateTime::now();

    $event = new BookmarkWasCreated($id, $imageId, $userId, $createdAt);
    $payload = $event->toPayload();

    $this->assertEquals($id->toString(), $payload['uuid']);
    $this->assertEquals($imageId->toString(), $payload['imageId']);
    $this->assertEquals($userId->toString(), $payload['userId']);
    $this->assertEquals($createdAt->toString(), $payload['createdAt']);
  }

  #[Test]
  public function itDeserializesFromPayload(): void {
    $id = ID::generate();
    $imageId = ID::generate();
    $userId = ID::generate();
    $createdAt = DateTime::now();

    $payload = [
      'uuid' => $id->toString(),
      'imageId' => $imageId->toString(),
      'userId' => $userId->toString(),
      'createdAt' => $createdAt->toString(),
    ];

    $event = BookmarkWasCreated::fromPayload($payload);

    $this->assertEquals($id->toString(), $event->id->toString());
    $this->assertEquals($imageId->toString(), $event->imageId->toString());
    $this->assertEquals($userId->toString(), $event->userId->toString());
    $this->assertEquals($createdAt->toString(), $event->createdAt->toString());
  }

  #[Test]
  public function itRoundTripsPayloadSerialization(): void {
    $original = new BookmarkWasCreated(
      ID::generate(),
      ID::generate(),
      ID::generate(),
      DateTime::now(),
    );

    $restored = BookmarkWasCreated::fromPayload($original->toPayload());

    $this->assertEquals($original->id->toString(), $restored->id->toString());
    $this->assertEquals($original->imageId->toString(), $restored->imageId->toString());
    $this->assertEquals($original->userId->toString(), $restored->userId->toString());
    $this->assertEquals($original->createdAt->toString(), $restored->createdAt->toString());
  }
}
