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
use Slink\Shared\Infrastructure\Pagination\CursorPaginator;
use Slink\Tag\Domain\Service\TagFilterServiceInterface;

final readonly class GetImageListHandler implements QueryHandlerInterface {
  use CursorPaginationTrait;

  public function __construct(
    private ImageRepositoryInterface  $repository,
    private TagFilterServiceInterface $tagFilterService,
    private ImageResourceProcessor    $resourceProcessor,
    private CursorPaginator           $cursorPaginator
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
   * @throws \Exception
   */
  public function __invoke(
    GetImageListQuery     $query,
    int                   $page,
    ?bool                 $isPublic = null,
    ?string               $userId = null,
    ?ImageResourceContext $resourceContext = null,
  ): Collection {
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

    $imageIds = iterator_map($images, fn(ImageView $image) => (string)$image->getUuid());

    $resourceContext ??= new ImageResourceContext();
    $items = $this->resourceProcessor->many($images, $resourceContext->withImageIds($imageIds));
    $paginator = $this->cursorPaginator->paginate($items, $query->getLimit());

    return Collection::fromCursorPaginator(
      $paginator,
      page: $page,
      limit: $query->getLimit(),
      total: $images->count()
    );
  }
}