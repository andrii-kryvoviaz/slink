<?php

declare(strict_types=1);

namespace Slink\Bookmark\Infrastructure\Resource;

use Slink\Bookmark\Infrastructure\ReadModel\View\BookmarkView;
use Slink\Shared\Infrastructure\Resource\AbstractResourceContext;

final readonly class BookmarkResourceContext extends AbstractResourceContext {
  /**
   * @param array<string> $groups
   * @param array<string> $imageIds
   */
  public function __construct(
    array        $groups = ['public'],
    public array $imageIds = [],
  ) {
    parent::__construct($groups);
  }

  /**
   * @param iterable<BookmarkView> $bookmarks
   */
  public function withBookmarks(iterable $bookmarks): self {
    $imageIds = [];

    foreach ($bookmarks as $bookmark) {
      $imageIds[] = $bookmark->getImageId();
    }

    return new self($this->getGroups(), $imageIds);
  }
}
