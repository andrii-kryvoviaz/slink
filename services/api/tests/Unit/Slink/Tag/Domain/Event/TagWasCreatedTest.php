<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Tag\Domain\Event;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Event\TagWasCreated;
use Slink\Tag\Domain\ValueObject\TagName;
use Slink\Tag\Domain\ValueObject\TagPath;

final class TagWasCreatedTest extends TestCase {

  #[Test]
  public function itCreatesEventWithAllProperties(): void {
    $id = ID::generate();
    $userId = ID::generate();
    $parentId = ID::generate();
    $name = TagName::fromString('test-tag');
    $path = TagPath::fromString('#parent/test-tag');
    $createdAt = DateTime::now();

    $event = new TagWasCreated($id, $userId, $name, $path, $parentId, $createdAt);

    $this->assertEquals($id, $event->id);
    $this->assertEquals($userId, $event->userId);
    $this->assertEquals($name, $event->name);
    $this->assertEquals($path, $event->path);
    $this->assertEquals($parentId, $event->parentId);
    $this->assertEquals($createdAt, $event->createdAt);
  }

  #[Test]
  public function itCreatesEventWithoutParentId(): void {
    $id = ID::generate();
    $userId = ID::generate();
    $name = TagName::fromString('root-tag');
    $path = TagPath::fromString('#root-tag');

    $event = new TagWasCreated($id, $userId, $name, $path);

    $this->assertEquals($id, $event->id);
    $this->assertEquals($userId, $event->userId);
    $this->assertEquals($name, $event->name);
    $this->assertEquals($path, $event->path);
    $this->assertNull($event->parentId);
    $this->assertNull($event->createdAt);
  }

  #[Test]
  public function itSerializesToPayload(): void {
    $id = ID::generate();
    $userId = ID::generate();
    $parentId = ID::generate();
    $name = TagName::fromString('serializable-tag');
    $path = TagPath::fromString('#parent/serializable-tag');
    $createdAt = DateTime::now();

    $event = new TagWasCreated($id, $userId, $name, $path, $parentId, $createdAt);
    $payload = $event->toPayload();

    $this->assertArrayHasKey('uuid', $payload);
    $this->assertArrayHasKey('user_id', $payload);
    $this->assertArrayHasKey('name', $payload);
    $this->assertArrayHasKey('path', $payload);
    $this->assertArrayHasKey('parent_id', $payload);
    $this->assertArrayHasKey('created_at', $payload);

    $this->assertEquals($id->toString(), $payload['uuid']);
    $this->assertEquals($userId->toString(), $payload['user_id']);
    $this->assertEquals('serializable-tag', $payload['name']);
    $this->assertEquals('#parent/serializable-tag', $payload['path']);
    $this->assertEquals($parentId->toString(), $payload['parent_id']);
    $this->assertEquals($createdAt->toString(), $payload['created_at']);
  }

  #[Test]
  public function itSerializesToPayloadWithoutParentId(): void {
    $id = ID::generate();
    $userId = ID::generate();
    $name = TagName::fromString('root-tag');
    $path = TagPath::fromString('#root-tag');

    $event = new TagWasCreated($id, $userId, $name, $path);
    $payload = $event->toPayload();

    $this->assertNull($payload['parent_id']);
    $this->assertIsString($payload['created_at']); // Should use DateTime::now()
  }

  #[Test]
  public function itDeserializesFromPayload(): void {
    $id = ID::generate();
    $userId = ID::generate();
    $parentId = ID::generate();
    $createdAt = DateTime::now();

    $payload = [
      'uuid' => $id->toString(),
      'user_id' => $userId->toString(),
      'name' => 'deserialized-tag',
      'path' => '#parent/deserialized-tag',
      'parent_id' => $parentId->toString(),
      'created_at' => $createdAt->toString(),
    ];

    $event = TagWasCreated::fromPayload($payload);

    $this->assertEquals($id->toString(), $event->id->toString());
    $this->assertEquals($userId->toString(), $event->userId->toString());
    $this->assertEquals('deserialized-tag', $event->name->getValue());
    $this->assertEquals('#parent/deserialized-tag', $event->path->getValue());
    $this->assertEquals($parentId->toString(), $event->parentId?->toString());
    $this->assertEquals($createdAt->toString(), $event->createdAt?->toString());
  }

  #[Test]
  public function itDeserializesFromPayloadWithoutParentId(): void {
    $id = ID::generate();
    $userId = ID::generate();
    $createdAt = DateTime::now();

    $payload = [
      'uuid' => $id->toString(),
      'user_id' => $userId->toString(),
      'name' => 'root-tag',
      'path' => '#root-tag',
      'created_at' => $createdAt->toString(),
    ];

    $event = TagWasCreated::fromPayload($payload);

    $this->assertEquals($id->toString(), $event->id->toString());
    $this->assertEquals($userId->toString(), $event->userId->toString());
    $this->assertEquals('root-tag', $event->name->getValue());
    $this->assertEquals('#root-tag', $event->path->getValue());
    $this->assertNull($event->parentId);
    $this->assertEquals($createdAt->toString(), $event->createdAt?->toString());
  }

  #[Test]
  public function itDeserializesFromPayloadWithoutCreatedAt(): void {
    $id = ID::generate();
    $userId = ID::generate();

    $payload = [
      'uuid' => $id->toString(),
      'user_id' => $userId->toString(),
      'name' => 'no-time-tag',
      'path' => '#no-time-tag',
    ];

    $event = TagWasCreated::fromPayload($payload);

    $this->assertEquals($id->toString(), $event->id->toString());
    $this->assertEquals($userId->toString(), $event->userId->toString());
    $this->assertEquals('no-time-tag', $event->name->getValue());
    $this->assertEquals('#no-time-tag', $event->path->getValue());
    $this->assertNull($event->parentId);
    $this->assertNull($event->createdAt);
  }

  #[Test]
  public function itHasPublicProperties(): void {
    $id = ID::generate();
    $userId = ID::generate();
    $name = TagName::fromString('property-tag');
    $path = TagPath::fromString('#property-tag');
    $createdAt = DateTime::now();

    $event = new TagWasCreated($id, $userId, $name, $path, null, $createdAt);

    $this->assertEquals($id, $event->id);
    $this->assertEquals($userId, $event->userId);
    $this->assertEquals($name, $event->name);
    $this->assertEquals($path, $event->path);
    $this->assertNull($event->parentId);
    $this->assertEquals($createdAt, $event->createdAt);
  }
}