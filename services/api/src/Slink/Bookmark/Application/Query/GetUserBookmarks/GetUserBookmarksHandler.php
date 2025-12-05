<?php

declare(strict_types=1);

namespace Slink\Bookmark\Application\Query\GetUserBookmarks;

use Slink\Bookmark\Domain\Repository\BookmarkRepositoryInterface;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Infrastructure\Pagination\CursorPaginationTrait;

final readonly class GetUserBookmarksHandler implements QueryHandlerInterface {
  use CursorPaginationTrait;

  public function __construct(
    private BookmarkRepositoryInterface $repository,
  ) {
  }

  public function __invoke(GetUserBookmarksQuery $query, string $userId): Collection {
    $bookmarks = $this->repository->findByUserId(
      $userId,
      $query->page,
      $query->getLimit(),
      $query->getCursor(),
    );

    $bookmarkEntities = iterator_to_array($bookmarks);

    $limit = $query->getLimit();
    $hasMore = count($bookmarkEntities) > $limit;

    if ($hasMore) {
      array_pop($bookmarkEntities);
    }

    $nextCursor = null;
    if ($hasMore && !empty($bookmarkEntities)) {
      $lastBookmark = end($bookmarkEntities);
      $nextCursor = $this->generateNextCursor($lastBookmark);
    }

    $items = array_map(fn($bookmark) => Item::fromEntity($bookmark), $bookmarkEntities);

    return new Collection(
      $query->page,
      $limit,
      $bookmarks->count(),
      $items,
      $nextCursor
    );
  }
}
