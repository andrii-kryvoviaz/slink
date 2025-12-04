<?php

declare(strict_types=1);

namespace Slink\Bookmark\Infrastructure\ReadModel\Projection;

use Doctrine\ORM\EntityManagerInterface;
use Slink\Bookmark\Domain\Event\BookmarkWasCreated;
use Slink\Bookmark\Domain\Event\BookmarkWasRemoved;
use Slink\Bookmark\Domain\Repository\BookmarkRepositoryInterface;
use Slink\Bookmark\Infrastructure\ReadModel\View\BookmarkView;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class BookmarkProjection extends AbstractProjection {
  public function __construct(
    private readonly BookmarkRepositoryInterface $bookmarkRepository,
    private readonly ImageRepositoryInterface $imageRepository,
    private readonly EntityManagerInterface $em,
  ) {
  }

  public function handleBookmarkWasCreated(BookmarkWasCreated $event): void {
    /** @var UserView $user */
    $user = $this->em->getReference(UserView::class, $event->userId->toString());
    /** @var ImageView $image */
    $image = $this->em->getReference(ImageView::class, $event->imageId->toString());

    $bookmark = new BookmarkView(
      $event->id->toString(),
      $user,
      $image,
      $event->createdAt,
    );

    $this->bookmarkRepository->add($bookmark);

    $imageView = $this->imageRepository->oneById($event->imageId->toString());
    $imageView->incrementBookmarkCount();
  }

  public function handleBookmarkWasRemoved(BookmarkWasRemoved $event): void {
    $bookmark = $this->bookmarkRepository->findById($event->id->toString());

    if ($bookmark === null) {
      return;
    }

    $imageId = $bookmark->getImageId();
    $this->bookmarkRepository->remove($bookmark);

    $imageView = $this->imageRepository->oneById($imageId);
    $imageView->decrementBookmarkCount();
  }
}
