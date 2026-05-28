<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageListExists;

use Slink\Image\Domain\Filter\ImageListFilter;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Tag\Domain\Service\TagFilterServiceInterface;

final readonly class GetImageListExistsHandler implements QueryHandlerInterface {
  public function __construct(
    private ImageRepositoryInterface  $repository,
    private TagFilterServiceInterface $tagFilterService,
  ) {
  }

  public function __invoke(
    GetImageListExistsQuery $query,
    ?bool                   $isPublic = null,
    ?string                 $userId = null,
  ): bool {
    $tagFilterData = $this->tagFilterService->createTagFilterData(
      $query->getTagIds(),
      $query->requireAllTags(),
      $userId,
    );

    $filter = new ImageListFilter(
      isPublic: $isPublic,
      userId: $userId,
      searchTerm: $query->getSearchTerm(),
      searchBy: $query->getSearchBy(),
      tagFilterData: $tagFilterData,
    );

    return $this->repository->existsByFilter($filter);
  }
}
