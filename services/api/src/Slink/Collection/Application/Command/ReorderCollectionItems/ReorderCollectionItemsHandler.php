<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Command\ReorderCollectionItems;

use Slink\Collection\Domain\Enum\CollectionAccess;
use Slink\Collection\Domain\Exception\CollectionAccessDeniedException;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class ReorderCollectionItemsHandler implements CommandHandlerInterface {
  public function __construct(
    private CollectionStoreRepositoryInterface $collectionStore,
    private AuthorizationCheckerInterface $access,
  ) {
  }

  public function __invoke(ReorderCollectionItemsCommand $command, string $userId): void {
    $collection = $this->collectionStore->get(ID::fromString($command->getCollectionId()));

    if (!$this->access->isGranted(CollectionAccess::ManageItems, $collection)) {
      throw new CollectionAccessDeniedException();
    }

    $collection->reorderItems($command->getOrderedItemIds());

    $this->collectionStore->store($collection);
  }
}
