<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Query\GetUserCollections;

use Slink\Collection\Domain\Filter\CollectionListFilter;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Collection\Domain\Service\CollectionCoverGeneratorInterface;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionView;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Infrastructure\Pagination\CursorPaginator;

final readonly class GetUserCollectionsHandler implements QueryHandlerInterface {
  public function __construct(
    private CollectionRepositoryInterface     $collectionRepository,
    private CollectionItemRepositoryInterface  $collectionItemRepository,
    private CollectionCoverGeneratorInterface  $coverGenerator,
    private CursorPaginator                    $cursorPaginator,
  ) {
  }

  public function __invoke(GetUserCollectionsQuery $query, string $userId): Collection {
    $filter = new CollectionListFilter(
      limit: $query->getLimit(),
      userId: $userId,
      searchTerm: $query->getSearchTerm(),
      cursor: $query->getCursor(),
    );

    $paginator = $this->collectionRepository->getByUserId($filter);

    $collections = iterator_to_array($paginator, preserve_keys: false);
    $collectionIds = array_map(static fn(CollectionView $c) => $c->getId(), $collections);
    $itemCounts = $this->collectionItemRepository->countByCollectionIds($collectionIds);
    $coverUrls = $this->coverGenerator->getCoverUrlsByIds($collectionIds);

    $items = array_map(
      static fn(CollectionView $c) => Item::fromEntity($c, [
        'itemCount' => $itemCounts[$c->getId()] ?? 0,
        'coverImage' => $coverUrls[$c->getId()] ?? null,
      ]),
      $collections,
    );

    $cursorResult = $this->cursorPaginator->paginate($items, $query->getLimit());
    $total = $this->collectionRepository->countByUserId($filter);

    return Collection::fromCursorPaginator($cursorResult, limit: $query->getLimit(), total: $total);
  }
}
