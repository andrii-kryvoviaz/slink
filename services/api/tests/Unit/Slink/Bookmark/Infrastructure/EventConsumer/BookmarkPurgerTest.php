<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Bookmark\Infrastructure\EventConsumer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Bookmark\Domain\Repository\BookmarkRepositoryInterface;
use Slink\Bookmark\Infrastructure\EventConsumer\BookmarkPurger;
use Slink\Image\Domain\Event\ImageWasDeleted;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Event\UserWasPurged;

final class BookmarkPurgerTest extends TestCase {

  #[Test]
  public function itDeletesBookmarksOfPurgedUser(): void {
    $userId = ID::generate();

    $bookmarkRepository = $this->createMock(BookmarkRepositoryInterface::class);
    $bookmarkRepository->expects($this->once())
      ->method('deleteByUserId')
      ->with($userId->toString());

    $consumer = new BookmarkPurger($bookmarkRepository);

    $consumer->handleUserWasPurged(new UserWasPurged($userId));
  }

  #[Test]
  public function itDeletesBookmarksOfDeletedImage(): void {
    $imageId = ID::generate();

    $bookmarkRepository = $this->createMock(BookmarkRepositoryInterface::class);
    $bookmarkRepository->expects($this->once())
      ->method('deleteByImageId')
      ->with($imageId->toString());

    $consumer = new BookmarkPurger($bookmarkRepository);

    $consumer->handleImageWasDeleted(new ImageWasDeleted($imageId, false));
  }
}
