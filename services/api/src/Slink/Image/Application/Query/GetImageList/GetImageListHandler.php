<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageList;

use Slink\Bookmark\Domain\Repository\BookmarkRepositoryInterface;
use Slink\Image\Domain\Filter\ImageListFilter;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Infrastructure\Pagination\CursorPaginationTrait;
use Slink\Tag\Domain\Service\TagFilterServiceInterface;

final readonly class GetImageListHandler implements QueryHandlerInterface {
  use CursorPaginationTrait;

  public function __construct(
    private ImageRepositoryInterface $repository,
    private TagFilterServiceInterface $tagFilterService,
    private BookmarkRepositoryInterface $bookmarkRepository,
  ) {
  }

  public function __invoke(
    GetImageListQuery $query,
    int $page,
    ?bool $isPublic = null,
    ?string $userId = null,
    ?string $currentUserId = null,
  ): Collection {
    $tagFilterData = $this->tagFilterService->createTagFilterData(
      $query->getTagIds(),
      $query->requireAllTags(),
      $userId
    );

    $images = $this->repository->geImageList($page, new ImageListFilter(
      limit: $query->getLimit(),
      orderBy: $query->getOrderBy(),
      order: $query->getOrder(),
      isPublic: $isPublic,
      userId: $userId,
      searchTerm: $query->getSearchTerm(),
      searchBy: $query->getSearchBy(),
      cursor: $query->getCursor(),
      tagFilterData: $tagFilterData,
    ));

    $imageEntities = iterator_to_array($images);

    $limit = $query->getLimit();
    $hasMore = count($imageEntities) > $limit;

    if ($hasMore) {
      array_pop($imageEntities);
    }

    $nextCursor = null;
    if ($hasMore && !empty($imageEntities)) {
      $lastImage = end($imageEntities);
      $nextCursor = $this->generateNextCursor($lastImage);
    }

    $bookmarkedSet = [];
    if ($currentUserId !== null && !empty($imageEntities)) {
      $imageIds = array_map(fn($image) => $image->getUuid(), $imageEntities);
      $bookmarkedSet = array_flip($this->bookmarkRepository->getBookmarkedImageIds($currentUserId, $imageIds));
    }

    $items = array_map(
      fn($image) => Item::fromEntity($image, ['isBookmarked' => isset($bookmarkedSet[$image->getUuid()])]),
      $imageEntities
    );

    return new Collection(
      $page,
      $limit,
      $images->count(),
      $items,
      $nextCursor
    );
  }
}