<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageList;

use Slink\Image\Domain\Filter\ImageListFilter;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Infrastructure\Pagination\CursorPaginationTrait;

final readonly class GetImageListHandler implements QueryHandlerInterface {
  use CursorPaginationTrait;

  public function __construct(
    private ImageRepositoryInterface $repository
  ) {
  }

  public function __invoke(GetImageListQuery $query, int $page, ?bool $isPublic = null, ?string $userId = null): Collection {
    $images = $this->repository->geImageList($page, new ImageListFilter(
      limit: $query->getLimit(),
      orderBy: $query->getOrderBy(),
      order: $query->getOrder(),
      isPublic: $isPublic,
      userId: $userId,
      searchTerm: $query->getSearchTerm(),
      searchBy: $query->getSearchBy(),
      cursor: $query->getCursor()
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

    $items = array_map(fn($image) => Item::fromEntity($image), $imageEntities);

    return new Collection(
      $page,
      $limit,
      $images->count(),
      $items,
      $nextCursor
    );
  }
}