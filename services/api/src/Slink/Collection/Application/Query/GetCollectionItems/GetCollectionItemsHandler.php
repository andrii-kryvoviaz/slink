<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Query\GetCollectionItems;

use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Infrastructure\Resource\CollectionItemResourceContext;
use Slink\Collection\Infrastructure\Resource\CollectionItemResourceProcessor;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Infrastructure\Pagination\CursorPaginator;

final readonly class GetCollectionItemsHandler implements QueryHandlerInterface {
  public function __construct(
    private CollectionItemRepositoryInterface $collectionItemRepository,
    private CollectionItemResourceProcessor $resourceProcessor,
    private CursorPaginator $cursorPaginator,
  ) {
  }

  public function __invoke(GetCollectionItemsQuery $query): Collection {
    $paginator = $this->collectionItemRepository->getCollectionItemsByCursor(
      $query->getCollectionId(),
      $query->getLimit(),
      $query->getCursor()
    );

    $items = iterator_to_array($paginator);
    $total = $this->collectionItemRepository->countCollectionItems($query->getCollectionId());

    $context = (new CollectionItemResourceContext())->withItems($items);
    $processedItems = $this->resourceProcessor->many($items, $context);
    $cursorResult = $this->cursorPaginator->paginate($processedItems, $query->getLimit());

    return Collection::fromCursorPaginator(
      $cursorResult,
      limit: $query->getLimit(),
      total: $total
    );
  }
}
