<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Command\ReorderCollectionItems;

use Slink\Collection\Domain\Collection;
use Slink\Collection\Domain\Enum\CollectionAccess;
use Slink\Collection\Domain\Exception\CollectionAccessDeniedException;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Application\Lock\ResourceLock;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class ReorderCollectionItemsHandler implements CommandHandlerInterface {
  public function __construct(
    private CollectionStoreRepositoryInterface $collectionStore,
    private AuthorizationCheckerInterface $access,
    private ResourceLock $lock,
  ) {
  }

  public function __invoke(ReorderCollectionItemsCommand $command, string $userId): void {
    $collectionId = ID::fromString($command->getCollectionId());

    $this->lock->run(Collection::itemsLockKey($collectionId), function () use ($command, $collectionId): void {
      $collection = $this->collectionStore->get($collectionId);

      if (!$this->access->isGranted(CollectionAccess::ManageItems, $collection)) {
        throw new CollectionAccessDeniedException();
      }

      $collection->reorderItems($command->getOrderedItemIds());

      $this->collectionStore->store($collection);
    });
  }
}
