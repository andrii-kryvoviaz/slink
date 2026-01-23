<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Query\GetUserCollections;

use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Collection\Domain\Service\CollectionCoverGeneratorInterface;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;

final readonly class GetUserCollectionsHandler implements QueryHandlerInterface {
  public function __construct(
    private CollectionRepositoryInterface $collectionRepository,
    private CollectionItemRepositoryInterface $collectionItemRepository,
    private CollectionCoverGeneratorInterface $coverGenerator,
  ) {
  }

  public function __invoke(GetUserCollectionsQuery $query): Collection {
    $paginator = $this->collectionRepository->getByUserId(
      $query->getUserId(),
      $query->getPage(),
      $query->getLimit(),
    );
    
    $collections = iterator_to_array($paginator);
    $collectionIds = array_map(fn($c) => $c->getId(), $collections);
    $itemCounts = $this->collectionItemRepository->countByCollectionIds($collectionIds);
    $coverImageIds = $this->collectionItemRepository->getFirstImageIdsByCollectionIds($collectionIds, 5);
    
    $items = array_map(
      fn($c) => Item::fromEntity($c, [
        'itemCount' => $itemCounts[$c->getId()] ?? 0,
        'coverImage' => $this->coverGenerator->getCoverUrl($c->getId(), $coverImageIds[$c->getId()] ?? []),
      ]),
      $collections
    );

    return new Collection(
      page: $query->getPage(),
      limit: $query->getLimit(),
      total: $paginator->count(),
      data: $items
    );
  }
}
