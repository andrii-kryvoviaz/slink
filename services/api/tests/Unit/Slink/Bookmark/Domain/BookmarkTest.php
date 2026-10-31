<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Bookmark\Domain;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Bookmark\Domain\Bookmark;
use Slink\Bookmark\Domain\Context\BookmarkCreationContext;
use Slink\Bookmark\Domain\Event\BookmarkWasRemoved;
use Slink\Bookmark\Domain\Exception\PrivateImageBookmarkException;
use Slink\Bookmark\Domain\Exception\SelfBookmarkException;
use Slink\Bookmark\Domain\Specification\PublicImageBookmarkSpecificationInterface;
use Slink\Bookmark\Domain\Specification\SelfBookmarkSpecificationInterface;
use Slink\Bookmark\Infrastructure\ReadModel\View\BookmarkView;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class BookmarkTest extends TestCase {
  #[Test]
  public function itCreatesBookmark(): void {
    $id = ID::generate();
    $imageId = ID::generate();
    $userId = ID::generate();

    $bookmark = Bookmark::create($id, $imageId, $userId, $this->createContext());

    $this->assertEquals($imageId, $bookmark->getImageId());
    $this->assertEquals($userId, $bookmark->getUserId());
    $this->assertInstanceOf(DateTime::class, $bookmark->getCreatedAt());
  }

  #[Test]
  public function itRemovesBookmark(): void {
    $bookmark = $this->createBookmark();
    $bookmark->releaseEvents();

    $bookmark->remove();

    $events = $bookmark->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(BookmarkWasRemoved::class, $events[0]);
  }

  #[Test]
  public function itThrowsExceptionWhenBookmarkingOwnImage(): void {
    $this->expectException(SelfBookmarkException::class);
    $this->expectExceptionMessage('You cannot bookmark your own image');

    $id = ID::generate();
    $imageId = ID::generate();
    $userId = ID::generate();

    $specification = $this->createMock(SelfBookmarkSpecificationInterface::class);
    $specification
      ->expects($this->once())
      ->method('ensureNotSelfBookmark')
      ->with($imageId, $userId)
      ->willThrowException(new SelfBookmarkException());

    $publicSpecification = $this->createStub(PublicImageBookmarkSpecificationInterface::class);

    $context = new BookmarkCreationContext($specification, $publicSpecification);

    Bookmark::create($id, $imageId, $userId, $context);
  }

  #[Test]
  public function itHasCorrectAggregateRootId(): void {
    $id = ID::generate();
    $bookmark = Bookmark::create($id, ID::generate(), ID::generate(), $this->createContext());

    $this->assertEquals($id->toString(), $bookmark->aggregateRootId()->toString());
  }

  #[Test]
  public function itCallsSpecificationOnCreate(): void {
    $id = ID::generate();
    $imageId = ID::generate();
    $userId = ID::generate();

    $specification = $this->createMock(SelfBookmarkSpecificationInterface::class);
    $specification
      ->expects($this->once())
      ->method('ensureNotSelfBookmark')
      ->with($imageId, $userId);

    $publicSpecification = $this->createStub(PublicImageBookmarkSpecificationInterface::class);

    $context = new BookmarkCreationContext($specification, $publicSpecification);

    Bookmark::create($id, $imageId, $userId, $context);
  }

  #[Test]
  public function itThrowsExceptionWhenBookmarkingPrivateImage(): void {
    $this->expectException(PrivateImageBookmarkException::class);
    $this->expectExceptionMessage('Cannot bookmark a private image');

    $id = ID::generate();
    $imageId = ID::generate();
    $userId = ID::generate();

    $selfBookmarkSpecification = $this->createStub(SelfBookmarkSpecificationInterface::class);
    $publicSpecification = $this->createMock(PublicImageBookmarkSpecificationInterface::class);
    $publicSpecification
      ->expects($this->once())
      ->method('ensureImageIsPublic')
      ->with($imageId)
      ->willThrowException(new PrivateImageBookmarkException());

    $context = new BookmarkCreationContext($selfBookmarkSpecification, $publicSpecification);

    Bookmark::create($id, $imageId, $userId, $context);
  }

  #[Test]
  public function itRetainsImageIdAfterCreation(): void {
    $imageId = ID::generate();
    $bookmark = Bookmark::create(ID::generate(), $imageId, ID::generate(), $this->createContext());

    $this->assertEquals($imageId->toString(), $bookmark->getImageId()->toString());
  }

  #[Test]
  public function itRetainsUserIdAfterCreation(): void {
    $userId = ID::generate();
    $bookmark = Bookmark::create(ID::generate(), ID::generate(), $userId, $this->createContext());

    $this->assertEquals($userId->toString(), $bookmark->getUserId()->toString());
  }

  #[Test]
  public function itCanBeRemovedMultipleTimes(): void {
    $bookmark = $this->createBookmark();
    $bookmark->releaseEvents();

    $bookmark->remove();
    $bookmark->remove();

    $this->assertCount(2, $bookmark->releaseEvents());
  }

  #[Test]
  #[Group('sc-1176')]
  public function itHydratesEveryFieldFromItsView(): void {
    $id = ID::generate();
    $imageId = ID::generate();
    $userId = ID::generate();
    $createdAt = DateTime::fromString('2021-02-03 04:05:06');

    $bookmark = Bookmark::fromView($this->createView($id, $imageId, $userId, $createdAt));

    $this->assertTrue($id->equals($bookmark->aggregateRootId()));
    $this->assertSame($imageId->toString(), $bookmark->getImageId()->toString());
    $this->assertSame($userId->toString(), $bookmark->getUserId()->toString());
    $this->assertTrue($createdAt->equals($bookmark->getCreatedAt()));
  }

  #[Test]
  #[Group('sc-1176')]
  public function itRecordsNoEventsWhenHydrated(): void {
    $bookmark = Bookmark::fromView($this->createView());

    $this->assertSame([], $bookmark->releaseEvents());
  }

  #[Test]
  #[Group('sc-1176')]
  public function itRemovesAHydratedBookmark(): void {
    $id = ID::generate();
    $bookmark = Bookmark::fromView($this->createView($id));

    $bookmark->remove();

    $events = $bookmark->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(BookmarkWasRemoved::class, $events[0]);
    $this->assertSame($id->toString(), $events[0]->id->toString());
  }

  #[Test]
  #[Group('sc-1176')]
  public function itKeepsNoRemovalFlag(): void {
    $bookmark = new \ReflectionClass(Bookmark::class);

    $this->assertFalse($bookmark->hasProperty('removed'));
    $this->assertFalse($bookmark->hasMethod('isRemoved'));
  }

  private function createView(
    ?ID $id = null,
    ?ID $imageId = null,
    ?ID $userId = null,
    ?DateTime $createdAt = null,
  ): BookmarkView {
    $user = $this->createStub(UserView::class);
    $user->method('getUuid')->willReturn(($userId ?? ID::generate())->toString());

    $image = $this->createStub(ImageView::class);
    $image->method('getUuid')->willReturn(($imageId ?? ID::generate())->toString());

    return new BookmarkView(
      ($id ?? ID::generate())->toString(),
      $user,
      $image,
      $createdAt ?? DateTime::now(),
    );
  }

  private function createBookmark(): Bookmark {
    return Bookmark::create(
      ID::generate(),
      ID::generate(),
      ID::generate(),
      $this->createContext(),
    );
  }

  private function createContext(): BookmarkCreationContext {
    $specification = $this->createStub(SelfBookmarkSpecificationInterface::class);
    $publicSpecification = $this->createStub(PublicImageBookmarkSpecificationInterface::class);

    return new BookmarkCreationContext($specification, $publicSpecification);
  }
}
