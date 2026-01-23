<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Query\GetCollection;

use Slink\Collection\Domain\Exception\CollectionAccessDeniedException;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class GetCollectionHandler implements QueryHandlerInterface {
  public function __construct(
    private CollectionRepositoryInterface $collectionRepository,
    private CollectionItemRepositoryInterface $collectionItemRepository,
  ) {
  }

  public function __invoke(GetCollectionQuery $query, string $userId): ?Item {
    $collection = $this->collectionRepository->findById($query->getId());

    if ($collection === null) {
      return null;
    }

    $ownerId = ID::fromString($collection->getUserId());
    $currentUserId = ID::fromString($userId);

    if (!$ownerId->equals($currentUserId)) {
      throw new CollectionAccessDeniedException();
    }

    $itemCount = $this->collectionItemRepository->countByCollectionId($query->getId());

    return Item::fromEntity($collection, ['itemCount' => $itemCount]);
  }
}
