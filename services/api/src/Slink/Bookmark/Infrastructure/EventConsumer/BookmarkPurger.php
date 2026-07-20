<?php

declare(strict_types=1);

namespace Slink\Bookmark\Infrastructure\EventConsumer;

use Slink\Bookmark\Domain\Repository\BookmarkRepositoryInterface;
use Slink\Image\Domain\Event\ImageWasDeleted;
use Slink\Shared\Infrastructure\MessageBus\Event\AbstractEventConsumer;
use Slink\User\Domain\Event\UserWasPurged;

final class BookmarkPurger extends AbstractEventConsumer {
  public function __construct(
    private readonly BookmarkRepositoryInterface $bookmarkRepository,
  ) {
  }

  public function handleUserWasPurged(UserWasPurged $event): void {
    $this->bookmarkRepository->deleteByUserId($event->id->toString());
  }

  public function handleImageWasDeleted(ImageWasDeleted $event): void {
    $this->bookmarkRepository->deleteByImageId($event->id->toString());
  }
}
