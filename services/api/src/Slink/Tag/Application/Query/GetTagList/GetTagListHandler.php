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
  ) {
  }

  public function __invoke(GetTagListQuery $query, string $userId): Collection {
    $userId = ID::fromString($userId);

    if ($query->getIds() !== null) {
      $tagEntities = $this->tagRepository->findExactTagsByIds($query->getIds(), $userId);
      $items = array_map(fn($tag) => Item::fromEntity($tag), $tagEntities);

      return new Collection(
        1,
        count($items),
        count($items),
        $items
      );
    }

    $filter = new TagListFilter(
      limit: $query->getLimit(),
      orderBy: $query->getOrderBy(),
      order: $query->getOrder(),
      userId: $userId->toString(),
      parentId: $query->getParentId(),
      searchTerm: $query->getSearchTerm(),
      rootOnly: $query->isRootOnly(),
      includeChildren: $query->shouldIncludeChildren(),
    );

    $page = $query->getPage() ?? 1;
    $paginator = $this->tagRepository->getAllByPage($page, $filter);
    $tagEntities = iterator_to_array($paginator);

    $items = array_map(fn($tag) => Item::fromEntity($tag), $tagEntities);

    return new Collection(
      $page,
      $query->getLimit() ?? 10,
      $paginator->count(),
      $items
    );
  }
}