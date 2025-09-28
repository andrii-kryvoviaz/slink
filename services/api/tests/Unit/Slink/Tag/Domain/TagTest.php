<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Tag\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Event\TagWasCreated;
use Slink\Tag\Domain\Event\TagWasDeleted;
use Slink\Tag\Domain\Tag;
use Slink\Tag\Domain\ValueObject\TagName;
use Slink\Tag\Domain\ValueObject\TagPath;

final class TagTest extends TestCase {

  #[Test]
  public function itCreatesRootTag(): void {
    $tagId = ID::generate();
    $userId = ID::generate();
    $tagName = TagName::fromString('root-tag');

    $tag = Tag::create($tagId, $userId, $tagName);

    $events = $tag->releaseEvents();
    $this->assertCount(1, $events);
    
    $event = $events[0];
    $this->assertInstanceOf(TagWasCreated::class, $event);
    $this->assertEquals($tagId, $event->id);
    $this->assertEquals($userId, $event->userId);
    $this->assertEquals($tagName, $event->name);
    $this->assertNull($event->parentId);
    $this->assertInstanceOf(TagPath::class, $event->path);
  }

  #[Test]
  public function itCreatesChildTag(): void {
    $tagId = ID::generate();
    $userId = ID::generate();
    $parentId = ID::generate();
    $tagName = TagName::fromString('child-tag');
    $parentPath = TagPath::fromString('parent/path');

    $tag = Tag::create($tagId, $userId, $tagName, $parentId, $parentPath);

    $events = $tag->releaseEvents();
    $this->assertCount(1, $events);
    
    $event = $events[0];
    $this->assertInstanceOf(TagWasCreated::class, $event);
    $this->assertEquals($tagId, $event->id);
    $this->assertEquals($userId, $event->userId);
    $this->assertEquals($tagName, $event->name);
    $this->assertEquals($parentId, $event->parentId);
    $this->assertInstanceOf(TagPath::class, $event->path);
  }

  #[Test]
  public function itDeletesTag(): void {
    $tagId = ID::generate();
    $userId = ID::generate();
    $tagName = TagName::fromString('deletable-tag');

    $tag = Tag::create($tagId, $userId, $tagName);
    $tag->delete();

    $events = $tag->releaseEvents();
    $this->assertCount(2, $events);
    
    $deleteEvent = $events[1];
    $this->assertInstanceOf(TagWasDeleted::class, $deleteEvent);
    $this->assertEquals($tagId, $deleteEvent->id);
  }

  #[Test]
  public function itDoesNotDeleteAlreadyDeletedTag(): void {
    $tagId = ID::generate();
    $userId = ID::generate();
    $tagName = TagName::fromString('already-deleted');

    $tag = Tag::create($tagId, $userId, $tagName);
    $tag->delete();
    $tag->delete();

    $events = $tag->releaseEvents();
    $this->assertCount(2, $events);
  }

  #[Test]
  public function itHasCorrectAggregateId(): void {
    $tagId = ID::generate();
    $userId = ID::generate();
    $tagName = TagName::fromString('test-tag');

    $tag = Tag::create($tagId, $userId, $tagName);

    $this->assertEquals($tagId, $tag->aggregateRootId());
  }

  #[Test]
  public function itRecordsCreationTimestamp(): void {
    $tagId = ID::generate();
    $userId = ID::generate();
    $tagName = TagName::fromString('timestamped-tag');

    $beforeCreation = DateTime::now();
    $tag = Tag::create($tagId, $userId, $tagName);
    $afterCreation = DateTime::now();

    $events = $tag->releaseEvents();
    $event = $events[0];
    
    $this->assertInstanceOf(TagWasCreated::class, $event);
    $this->assertInstanceOf(DateTime::class, $event->createdAt);
    $this->assertGreaterThanOrEqual($beforeCreation->toDateTimeImmutable(), $event->createdAt->toDateTimeImmutable());
    $this->assertLessThanOrEqual($afterCreation->toDateTimeImmutable(), $event->createdAt->toDateTimeImmutable());
  }
}