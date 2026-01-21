<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Command\ReorderCollectionItems;

use Slink\Collection\Domain\Exception\CollectionAccessDeniedException;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class ReorderCollectionItemsHandler implements CommandHandlerInterface {
  public function __construct(
    private CollectionStoreRepositoryInterface $collectionStore,
  ) {
  }

  public function __invoke(ReorderCollectionItemsCommand $command, string $userId): void {
    $collection = $this->collectionStore->get(ID::fromString($command->getCollectionId()));

    if (!$collection->isOwnedBy(ID::fromString($userId))) {
      throw new CollectionAccessDeniedException();
    }

    $collection->reorderItems($command->getOrderedItemIds());

    $this->collectionStore->store($collection);
  }
}
