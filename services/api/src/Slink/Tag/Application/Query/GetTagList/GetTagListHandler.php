<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Query\GetTagList;

use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Filter\TagListFilter;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;

final readonly class GetTagListHandler implements QueryHandlerInterface {
  public function __construct(
    private TagRepositoryInterface $tagRepository,
  ) {}

  public function __invoke(GetTagListQuery $query, string $userId): Collection {
    $filter = new TagListFilter(
      limit: $query->getLimit(),
      orderBy: $query->getOrderBy(),
      order: $query->getOrder(),
      userId: $userId,
      parentId: $query->getParentId(),
      searchTerm: $query->getSearchTerm(),
      rootOnly: $query->isRootOnly(),
      includeChildren: $query->shouldIncludeChildren(),
    );

    $paginator = $this->tagRepository->getAllByPage(1, $filter);
    $tagEntities = iterator_to_array($paginator);

    $items = array_map(fn($tag) => Item::fromEntity($tag), $tagEntities);

    return new Collection(
      1,
      $query->getLimit(),
      $paginator->count(),
      $items
    );
  }
}