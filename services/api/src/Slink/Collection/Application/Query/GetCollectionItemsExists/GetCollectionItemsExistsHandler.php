<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Query\GetCollectionItemsExists;

use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Shared\Application\Query\QueryHandlerInterface;

final readonly class GetCollectionItemsExistsHandler implements QueryHandlerInterface {
  public function __construct(
    private CollectionItemRepositoryInterface $collectionItemRepository,
  ) {
  }

  public function __invoke(GetCollectionItemsExistsQuery $query): bool {
    return $this->collectionItemRepository->existsByCollectionId($query->getCollectionId());
  }
}
