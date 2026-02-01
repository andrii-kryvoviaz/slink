<?php

declare(strict_types=1);

namespace Slink\Bookmark\Application\Query\GetImageBookmarkers;

use Slink\Bookmark\Domain\Repository\BookmarkRepositoryInterface;
use Slink\Bookmark\Infrastructure\ReadModel\View\BookmarkView;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Pagination\CursorPaginationTrait;
use Slink\Shared\Infrastructure\Pagination\CursorPaginator;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class GetImageBookmarkersHandler implements QueryHandlerInterface {
  use CursorPaginationTrait;

  public function __construct(
    private BookmarkRepositoryInterface $bookmarkRepository,
    private ImageRepositoryInterface    $imageRepository,
    private CursorPaginator             $cursorPaginator,
  ) {
  }

  /**
   * @throws NotFoundException
   */
  public function __invoke(GetImageBookmarkersQuery $query, string $userId): Collection {
    $image = $this->imageRepository->oneById($query->imageId);

    if ($image->getUser()?->getUuid() !== $userId) {
      throw new AccessDeniedException('You can only view bookmarkers for your own images');
    }

    $bookmarks = $this->bookmarkRepository->findByImageId(
      $query->imageId,
      $query->getLimit(),
      $query->getCursor(),
    );

    $total = $this->bookmarkRepository->countByImageId($query->imageId);

    $items = iterator_map(
      $bookmarks,
      fn(BookmarkView $bookmark) => Item::fromEntity($bookmark, groups: ['bookmarkers'])
    );

    $paginator = $this->cursorPaginator->paginate($items, $query->getLimit());

    return Collection::fromCursorPaginator(
      $paginator,
      limit: $query->getLimit(),
      total: $total
    );
  }
}
