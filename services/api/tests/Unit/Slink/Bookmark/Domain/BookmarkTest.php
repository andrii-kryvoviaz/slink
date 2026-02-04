<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Bookmark\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Bookmark\Domain\Bookmark;
use Slink\Bookmark\Domain\Context\BookmarkCreationContext;
use Slink\Bookmark\Domain\Exception\PrivateImageBookmarkException;
use Slink\Bookmark\Domain\Exception\SelfBookmarkException;
use Slink\Bookmark\Domain\Specification\PublicImageBookmarkSpecificationInterface;
use Slink\Bookmark\Domain\Specification\SelfBookmarkSpecificationInterface;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

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
    $this->assertFalse($bookmark->isRemoved());
  }

  #[Test]
  public function itRemovesBookmark(): void {
    $bookmark = $this->createBookmark();

    $this->assertFalse($bookmark->isRemoved());

    $bookmark->remove();

    $this->assertTrue($bookmark->isRemoved());
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

    $bookmark->remove();
    $this->assertTrue($bookmark->isRemoved());

    $bookmark->remove();
    $this->assertTrue($bookmark->isRemoved());
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
