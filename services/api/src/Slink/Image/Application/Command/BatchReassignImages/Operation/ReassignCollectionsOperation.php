<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\BatchReassignImages\Operation;

use Slink\Collection\Domain\Enum\ItemType;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Image\Application\Command\BatchReassignImages\BatchReassignImagesCommand;
use Slink\Image\Domain\Image;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('batch_reassign.operation')]
final class ReassignCollectionsOperation implements BatchReassignOperationInterface {
  public function __construct(
    private readonly CollectionStoreRepositoryInterface $collectionRepository,
    private readonly CollectionItemRepositoryInterface $collectionItemRepository,
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

    $toAdd = array_diff($desiredIds, $currentIds);
    $toRemove = array_diff($currentIds, $desiredIds);

    $collections = $this->collectionRepository->getByIds(array_merge($toAdd, $toRemove));

    foreach ($collections->keys() as $key) {
      if (!$collections->get($key)->isOwnedBy($userId)) {
        $collections->remove($key);
      }
    }

    foreach ($toAdd as $collectionId) {
      if ($collections->has($collectionId)) {
        $collections->get($collectionId)->addItem($image->aggregateRootId(), ItemType::Image);
      }
    }

    foreach ($toRemove as $collectionId) {
      if ($collections->has($collectionId)) {
        $collections->get($collectionId)->removeItem($image->aggregateRootId());
      }
    }

    foreach ($collections->values() as $collection) {
      $this->collectionRepository->store($collection);
    }
  }
}
