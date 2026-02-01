<?php

declare(strict_types=1);

namespace Slink\Bookmark\Application\Query\GetUserBookmarks;

use Slink\Bookmark\Domain\Repository\BookmarkRepositoryInterface;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Infrastructure\Pagination\CursorPaginationTrait;
use Slink\Shared\Infrastructure\Pagination\CursorPaginator;

final readonly class GetUserBookmarksHandler implements QueryHandlerInterface {
  use CursorPaginationTrait;

  public function __construct(
    private BookmarkRepositoryInterface $repository,
    private CursorPaginator             $cursorPaginator,
  ) {
  }

  /**
   * @throws \Exception
   */
  public function __invoke(GetUserBookmarksQuery $query, string $userId): Collection {
    $bookmarks = $this->repository->findByUserId(
      $userId,
      $query->getLimit(),
      $query->getCursor(),
    );

    $total = $this->repository->countByUserId($userId);

    $items = iterator_map($bookmarks, fn($bookmark) => Item::fromEntity($bookmark));
    $paginator = $this->cursorPaginator->paginate($items, $query->getLimit());

    return Collection::fromCursorPaginator(
      $paginator,
      limit: $query->getLimit(),
      total: $total
    );
  }
}
