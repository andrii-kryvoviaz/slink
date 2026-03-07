<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\BatchImages\Operation;

use Slink\Collection\Domain\Enum\ItemType;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Image\Application\Command\BatchImages\BatchImagesCommand;
use Slink\Image\Domain\Image;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[AutoconfigureTag('batch_image.operation')]
final readonly class AddToCollectionOperation implements BatchImageOperationInterface {
  public function __construct(
    private CollectionStoreRepositoryInterface $collectionRepository,
  ) {}

  public function supports(BatchImagesCommand $command): bool {
    return $command->collectionId() !== null;
  }

  public function apply(Image $image, BatchImagesCommand $command, ID $userId): void {
    $collectionId = $command->collectionId();
    if ($collectionId === null) {
      return;
    }

    $collection = $this->collectionRepository->get(ID::fromString($collectionId));

    if (!$collection->isOwnedBy($userId)) {
      throw new AccessDeniedException();
    }

    $collection->addItem($image->aggregateRootId(), ItemType::Image);
    $this->collectionRepository->store($collection);
  }
}
