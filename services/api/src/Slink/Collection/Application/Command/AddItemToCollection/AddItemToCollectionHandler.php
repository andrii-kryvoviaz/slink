<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Command\AddItemToCollection;

use Slink\Collection\Domain\Enum\CollectionAccess;
use Slink\Collection\Domain\Exception\CollectionAccessDeniedException;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Image\Domain\Enum\ImageAccess;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\Exception\ForbiddenException;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class AddItemToCollectionHandler implements CommandHandlerInterface {
  public function __construct(
    private CollectionStoreRepositoryInterface $collectionStore,
    private ImageStoreRepositoryInterface $imageStore,
    private AuthorizationCheckerInterface $access,
    private LockFactory $lockFactory,
  ) {
  }

  public function __invoke(AddItemToCollectionCommand $command, string $userId): void {
    $collectionId = $command->getCollectionId();

    $lock = $this->lockFactory->createLock('collection-items-' . $collectionId);
    $lock->acquire(true);

    try {
      $collection = $this->collectionStore->get(ID::fromString($collectionId));

      if (!$this->access->isGranted(CollectionAccess::ManageItems, $collection)) {
        throw new CollectionAccessDeniedException();
      }

      $itemId = ID::fromString($command->getItemId());
      $image = $this->imageStore->get($itemId);

      if (!$this->access->isGranted(ImageAccess::Edit, $image)) {
        throw new ForbiddenException();
      }

      $collection->addItem($itemId, $command->getItemType());

      $this->collectionStore->store($collection);
    } finally {
      $lock->release();
    }
  }
}
