<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Query\GetTagListExists;

use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Tag\Domain\Filter\TagListFilter;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;

final readonly class GetTagListExistsHandler implements QueryHandlerInterface {
  public function __construct(
    private TagRepositoryInterface $tagRepository,
  ) {
  }

  public function __invoke(GetTagListExistsQuery $query, string $userId): bool {
    $filter = new TagListFilter(
      userId: $userId,
      parentId: $query->getParentId(),
      searchTerm: $query->getSearchTerm(),
      rootOnly: $query->isRootOnly(),
      ids: $query->getIds(),
    );

    return $this->tagRepository->existsByFilter($filter);
  }
}
