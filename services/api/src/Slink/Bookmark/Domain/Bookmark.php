<?php

declare(strict_types=1);

namespace Slink\Bookmark\Domain;

use Slink\Bookmark\Domain\Context\BookmarkCreationContext;
use Slink\Bookmark\Domain\Event\BookmarkWasCreated;
use Slink\Bookmark\Domain\Event\BookmarkWasRemoved;
use Slink\Bookmark\Infrastructure\ReadModel\View\BookmarkView;
use Slink\Shared\Domain\AbstractAggregateRoot;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final class Bookmark extends AbstractAggregateRoot {
  private ID $imageId;
  private ID $userId;
  private DateTime $createdAt;

  public static function create(
    ID $id,
    ID $imageId,
    ID $userId,
    BookmarkCreationContext $context,
  ): self {
    $context->selfBookmarkSpecification->ensureNotSelfBookmark($imageId, $userId);
    $context->publicImageBookmarkSpecification->ensureImageIsPublic($imageId);

    $bookmark = new self($id);
    $now = DateTime::now();

    $bookmark->recordThat(new BookmarkWasCreated(
      $id,
      $imageId,
      $userId,
      $now,
    ));

    return $bookmark;
  }

  public static function fromView(BookmarkView $view): self {
    $bookmark = new self(ID::fromString($view->getId()));
    $bookmark->imageId = ID::fromString($view->getImageId());
    $bookmark->userId = ID::fromString($view->getUserId());
    $bookmark->createdAt = $view->getCreatedAt();

    return $bookmark;
  }

  public function remove(): void {
    $this->recordThat(new BookmarkWasRemoved(
      $this->aggregateRootId(),
      DateTime::now(),
    ));
  }

  public function applyBookmarkWasCreated(BookmarkWasCreated $event): void {
    $this->imageId = $event->imageId;
    $this->userId = $event->userId;
    $this->createdAt = $event->createdAt;
  }

  public function getImageId(): ID {
    return $this->imageId;
  }

  public function getUserId(): ID {
    return $this->userId;
  }

  public function getCreatedAt(): DateTime {
    return $this->createdAt;
  }
}
