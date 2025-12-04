<?php

declare(strict_types=1);

namespace Slink\Bookmark\Infrastructure\Repository;

use Slink\Bookmark\Domain\Bookmark;
use Slink\Bookmark\Domain\Repository\BookmarkStoreRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\EventStore\AbstractSnapshotStoreRepository;

final class BookmarkStoreRepository extends AbstractSnapshotStoreRepository implements BookmarkStoreRepositoryInterface {
  protected static function getAggregateRootClass(): string {
    return Bookmark::class;
  }

  public function store(Bookmark $bookmark): void {
    $this->persist($bookmark);
  }

  public function get(ID $id): Bookmark {
    $bookmark = $this->retrieve($id);
    if (!$bookmark instanceof Bookmark) {
      throw new \RuntimeException('Expected instance of Bookmark, got ' . get_class($bookmark));
    }
    return $bookmark;
  }
}
