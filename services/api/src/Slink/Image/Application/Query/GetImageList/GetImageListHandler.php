<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageList;

use Slink\Image\Domain\Filter\ImageListFilter;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Image\Infrastructure\Resource\ImageResourceContext;
use Slink\Image\Infrastructure\Resource\ImageResourceProcessor;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Infrastructure\Pagination\CursorPaginationTrait;
use Slink\Tag\Domain\Service\TagFilterServiceInterface;

final readonly class GetImageListHandler implements QueryHandlerInterface {
  use CursorPaginationTrait;

  public function __construct(
    private ImageRepositoryInterface  $repository,
    private TagFilterServiceInterface $tagFilterService,
    private ImageResourceProcessor    $resourceProcessor,
  ) {
  }

  /**
   * @param GetImageListQuery $query
   * @param int $page
   * @param bool|null $isPublic
   * @param string|null $userId
   * @param ImageResourceContext|null $resourceContext
   * @return Collection
   * @throws \JsonException
   */
  public function __invoke(
    GetImageListQuery     $query,
    int                   $page,
    ?bool                 $isPublic = null,
    ?string               $userId = null,
    ?ImageResourceContext $resourceContext = null,
  ): Collection {
    $resourceContext ??= new ImageResourceContext();

    $tagFilterData = $this->tagFilterService->createTagFilterData(
      $query->getTagIds(),
      $query->requireAllTags(),
      $userId,
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

    $imageIds = array_map(fn(ImageView $img) => (string)$img->getUuid(), $imageEntities);
    $items = $this->resourceProcessor->many($imageEntities, $resourceContext->withImageIds($imageIds));

    return new Collection(
      $page,
      $limit,
      $images->count(),
      $items,
      $nextCursor
    );
  }
}