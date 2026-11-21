<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Tag\Domain;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Event\TagWasCreated;
use Slink\Tag\Domain\Event\TagWasDeleted;
use Slink\Tag\Domain\Event\TagWasMoved;
use Slink\Tag\Domain\Tag;
use Slink\Tag\Domain\ValueObject\TagName;
use Slink\Tag\Domain\ValueObject\TagPath;
use Slink\Tag\Infrastructure\ReadModel\View\TagView;



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

  #[Test]
  public function itDeletesTagWithChildIds(): void {
    $tagId = ID::generate();
    $userId = ID::generate();
    $tagName = TagName::fromString('parent-tag');

    $tag = Tag::create($tagId, $userId, $tagName);
    $tag->delete(['child-1', 'child-2']);

    $events = $tag->releaseEvents();
    $this->assertCount(2, $events);

    $deleteEvent = $events[1];
    $this->assertInstanceOf(TagWasDeleted::class, $deleteEvent);
    $this->assertEquals(['child-1', 'child-2'], $deleteEvent->directChildIds);
    $this->assertEquals($userId->toString(), $deleteEvent->userId);
  }

  #[Test]
  public function itDeletesTagWithUserId(): void {
    $tagId = ID::generate();
    $userId = ID::generate();
    $tagName = TagName::fromString('owned-tag');

    $tag = Tag::create($tagId, $userId, $tagName);
    $tag->delete();

    $events = $tag->releaseEvents();
    $deleteEvent = $events[1];
    $this->assertInstanceOf(TagWasDeleted::class, $deleteEvent);
    $this->assertEquals($userId->toString(), $deleteEvent->userId);
  }

  #[Test]
  public function itMovesTag(): void {
    $tagId = ID::generate();
    $userId = ID::generate();
    $tagName = TagName::fromString('movable-tag');
    $newParentId = ID::generate();
    $newPath = TagPath::fromString('#new-parent/movable-tag');

    $tag = Tag::create($tagId, $userId, $tagName);
    $tag->move($newParentId, $newPath);

    $events = $tag->releaseEvents();
    $this->assertCount(2, $events);

    $this->assertInstanceOf(TagWasCreated::class, $events[0]);

    $moveEvent = $events[1];
    $this->assertInstanceOf(TagWasMoved::class, $moveEvent);
    $this->assertEquals($newParentId, $moveEvent->newParentId);
    $this->assertEquals($newPath, $moveEvent->newPath);
  }

  #[Test]
  public function itMovesToRoot(): void {
    $tagId = ID::generate();
    $userId = ID::generate();
    $parentId = ID::generate();
    $tagName = TagName::fromString('child-tag');
    $parentPath = TagPath::fromString('#parent');
    $newPath = TagPath::fromString('#child-tag');

    $tag = Tag::create($tagId, $userId, $tagName, $parentId, $parentPath);
    $tag->move(null, $newPath);

    $events = $tag->releaseEvents();
    $this->assertCount(2, $events);

    $moveEvent = $events[1];
    $this->assertInstanceOf(TagWasMoved::class, $moveEvent);
    $this->assertNull($moveEvent->newParentId);
    $this->assertEquals($newPath, $moveEvent->newPath);
  }

  #[Test]
  #[Group('sc-1175')]
  public function itHydratesEveryFieldFromItsView(): void {
    $tagId = ID::generate();
    $ownerId = ID::generate();
    $parentId = ID::generate();
    $createdAt = DateTime::fromString('2021-02-03 04:05:06');
    $updatedAt = DateTime::fromString('2022-03-04 05:06:07');

    $tag = Tag::fromView(new TagView(
      $tagId->toString(),
      'child',
      '#parent/child',
      $ownerId->toString(),
      $parentId->toString(),
      $createdAt,
      $updatedAt,
    ));

    $this->assertTrue($tagId->equals($tag->aggregateRootId()));
    $this->assertSame('child', $tag->getName()->getValue());
    $this->assertSame('#parent/child', $tag->getPath()->getValue());
    $this->assertSame($ownerId->toString(), $tag->getUserId()->toString());
    $this->assertSame($parentId->toString(), $tag->getParentId()?->toString());
    $this->assertTrue($createdAt->equals($tag->getCreatedAt()));
    $this->assertTrue($updatedAt->equals($tag->getUpdatedAt()));
  }

  #[Test]
  #[Group('sc-1175')]
  public function itHydratesARootTagWithoutParentOrTimestamps(): void {
    $tag = Tag::fromView(new TagView(ID::generate()->toString(), 'root', '#root', ID::generate()->toString()));

    $this->assertNull($tag->getParentId());
    $this->assertFalse($tag->hasParent());
    $this->assertNull($tag->getCreatedAt());
    $this->assertNull($tag->getUpdatedAt());
  }

  #[Test]
  #[Group('sc-1175')]
  public function itRecordsNoEventsWhenHydrated(): void {
    $tag = Tag::fromView(new TagView(ID::generate()->toString(), 'root', '#root', ID::generate()->toString()));

    $this->assertSame([], $tag->releaseEvents());
  }

  #[Test]
  #[Group('sc-1175')]
  public function itDeletesAHydratedTagInTheNameOfItsOwner(): void {
    $tagId = ID::generate();
    $ownerId = ID::generate();
    $tag = Tag::fromView(new TagView($tagId->toString(), 'root', '#root', $ownerId->toString()));

    $tag->delete(['child-1']);

    $events = $tag->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(TagWasDeleted::class, $events[0]);
    $this->assertSame($tagId->toString(), $events[0]->id->toString());
    $this->assertSame($ownerId->toString(), $events[0]->userId);
    $this->assertSame(['child-1'], $events[0]->directChildIds);
  }

  #[Test]
  #[Group('sc-1175')]
  public function itMovesAHydratedTag(): void {
    $tag = Tag::fromView(new TagView(ID::generate()->toString(), 'root', '#root', ID::generate()->toString()));
    $newParentId = ID::generate();
    $newPath = TagPath::fromString('#bucket/root');

    $tag->move($newParentId, $newPath);

    $this->assertSame('#bucket/root', $tag->getPath()->getValue());
    $this->assertSame($newParentId->toString(), $tag->getParentId()?->toString());
    $events = $tag->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(TagWasMoved::class, $events[0]);
  }

  #[Test]
  #[Group('sc-1175')]
  public function itRecordsEveryDeleteSinceTheStoreGuardsMissingTags(): void {
    $tag = Tag::create(ID::generate(), ID::generate(), TagName::fromString('deleted-twice'));
    $tag->releaseEvents();

    $tag->delete();
    $tag->delete();

    $this->assertCount(2, $tag->releaseEvents());
  }
}
