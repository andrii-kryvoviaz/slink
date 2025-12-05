<?php

declare(strict_types=1);

namespace Slink\Bookmark\Application\Query\GetImageBookmarkers;

use Slink\Bookmark\Domain\Repository\BookmarkRepositoryInterface;
use Slink\Bookmark\Infrastructure\ReadModel\View\BookmarkView;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Infrastructure\Pagination\CursorPaginationTrait;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class GetImageBookmarkersHandler implements QueryHandlerInterface {
  use CursorPaginationTrait;

  public function __construct(
    private BookmarkRepositoryInterface $bookmarkRepository,
    private ImageRepositoryInterface $imageRepository,
  ) {
  }

  public function __invoke(GetImageBookmarkersQuery $query, string $userId): Collection {
    $image = $this->imageRepository->oneById($query->imageId);

    if ($image->getUser()?->getUuid() !== $userId) {
      throw new AccessDeniedException('You can only view bookmarkers for your own images');
    }

    $bookmarks = $this->bookmarkRepository->findByImageId(
      $query->imageId,
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

    $items = array_map(
      fn(BookmarkView $bookmark) => Item::fromEntity($bookmark, groups: ['bookmarkers']),
      $bookmarkEntities
    );

    return new Collection(
      $query->page,
      $limit,
      $bookmarks->count(),
      $items,
      $nextCursor
    );
  }
}
