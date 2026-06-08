<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Command\AddItemToCollection;

use Slink\Collection\Domain\Enum\CollectionAccess;
use Slink\Collection\Domain\Exception\CollectionAccessDeniedException;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class AddItemToCollectionHandler implements CommandHandlerInterface {
  public function __construct(
    private CollectionStoreRepositoryInterface $collectionStore,
    private AuthorizationCheckerInterface $access,
    private LockFactory $lockFactory,
  ) {
  }

  public function __invoke(AddItemToCollectionCommand $command, string $userId): void {
    $collectionId = $command->getCollectionId();

    $lock = $this->lockFactory->createLock('collection-add-' . $collectionId);
    $lock->acquire(true);

    try {
      $collection = $this->collectionStore->get(ID::fromString($collectionId));

      if (!$this->access->isGranted(CollectionAccess::ManageItems, $collection)) {
        throw new CollectionAccessDeniedException();
      }

      $collection->addItem(
        ID::fromString($command->getItemId()),
        $command->getItemType(),
      );

      $this->collectionStore->store($collection);
    } finally {
      $lock->release();
    }
  }
}
