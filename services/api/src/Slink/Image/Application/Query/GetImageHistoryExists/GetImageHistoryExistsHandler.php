<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageHistoryExists;

use Slink\Image\Domain\Filter\ImageListFilter;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Tag\Domain\Service\TagFilterServiceInterface;

final readonly class GetImageHistoryExistsHandler implements QueryHandlerInterface {
  public function __construct(
    private ImageRepositoryInterface  $repository,
    private TagFilterServiceInterface $tagFilterService,
  ) {
  }

  public function __invoke(
    GetImageHistoryExistsQuery $query,
    string                     $userId,
  ): bool {
    $tagFilterData = $this->tagFilterService->createTagFilterData(
      $query->getTagIds(),
      $query->requireAllTags(),
      $userId,
    );

    $filter = new ImageListFilter(
      userId: $userId,
      searchTerm: $query->getSearchTerm(),
      searchBy: $query->getSearchBy(),
      tagFilterData: $tagFilterData,
    );

    return $this->repository->existsByFilter($filter);
  }
}
