<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\BatchReassignImages\Operation;

use Slink\Collection\Domain\Collection;
use Slink\Collection\Domain\Enum\ItemType;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Image\Application\Command\BatchReassignImages\BatchReassignImagesCommand;
use Slink\Image\Domain\Image;
use Slink\Shared\Application\Lock\ResourceLock;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('batch_reassign.operation')]
final class ReassignCollectionsOperation implements BatchReassignOperationInterface {
  public function __construct(
    private readonly CollectionStoreRepositoryInterface $collectionRepository,
    private readonly CollectionItemRepositoryInterface $collectionItemRepository,
    private readonly ResourceLock $lock,
  ) {}

  public function supports(BatchReassignImagesCommand $command, string $imageId): bool {
    return $command->getCollectionIdsForImage($imageId) !== null;
  }

  public function apply(Image $image, BatchReassignImagesCommand $command, string $imageId, ID $userId): void {
    $desiredIds = $command->getCollectionIdsForImage($imageId);
    if ($desiredIds === null) {
      return;
    }

    $currentIds = $this->collectionItemRepository->getCollectionIdsByImageIds([$imageId])[$imageId] ?? [];
    $itemId = $image->aggregateRootId();

    foreach (array_diff($desiredIds, $currentIds) as $collectionId) {
      $this->changeCollection($collectionId, $userId, static function (Collection $collection) use ($itemId): void {
        $collection->addItem($itemId, ItemType::Image);
      });
    }

    foreach (array_diff($currentIds, $desiredIds) as $collectionId) {
      $this->changeCollection($collectionId, $userId, static function (Collection $collection) use ($itemId): void {
        $collection->removeItem($itemId);
      });
    }
  }

  private function changeCollection(string $collectionId, ID $userId, \Closure $change): void {
    $this->lock->run(Collection::itemsLockKey(ID::fromString($collectionId)), function () use ($collectionId, $userId, $change): void {
      $collections = $this->collectionRepository->getByIds([$collectionId]);

      if (!$collections->has($collectionId)) {
        return;
      }

      $collection = $collections->get($collectionId);

      if (!$collection->isOwnedBy($userId)) {
        return;
      }

      $change($collection);

      $this->collectionRepository->store($collection);
    });
  }
}
