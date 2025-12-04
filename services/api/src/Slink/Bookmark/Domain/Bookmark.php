<?php

declare(strict_types=1);

namespace Slink\Bookmark\Domain;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use Slink\Bookmark\Domain\Context\BookmarkCreationContext;
use Slink\Bookmark\Domain\Event\BookmarkWasCreated;
use Slink\Bookmark\Domain\Event\BookmarkWasRemoved;
use Slink\Shared\Domain\AbstractAggregateRoot;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final class Bookmark extends AbstractAggregateRoot {
  private ID $imageId;
  private ID $userId;
  private DateTime $createdAt;
  private bool $removed = false;

  public static function create(
    ID $id,
    ID $imageId,
    ID $userId,
    BookmarkCreationContext $context,
  ): self {
    $context->selfBookmarkSpecification->ensureNotSelfBookmark($imageId, $userId);

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

  public function applyBookmarkWasRemoved(BookmarkWasRemoved $event): void {
    $this->removed = true;
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

  public function isRemoved(): bool {
    return $this->removed;
  }

  protected function createSnapshotState(): array {
    return [
      'imageId' => $this->imageId->toString(),
      'userId' => $this->userId->toString(),
      'createdAt' => $this->createdAt->toString(),
      'removed' => $this->removed,
    ];
  }

  protected static function reconstituteFromSnapshotState(AggregateRootId $id, mixed $state): AggregateRootWithSnapshotting {
    $bookmark = new self(ID::fromString($id->toString()));
    $bookmark->imageId = ID::fromString($state['imageId']);
    $bookmark->userId = ID::fromString($state['userId']);
    $bookmark->createdAt = DateTime::fromString($state['createdAt']);
    $bookmark->removed = $state['removed'] ?? false;

    return $bookmark;
  }
}
