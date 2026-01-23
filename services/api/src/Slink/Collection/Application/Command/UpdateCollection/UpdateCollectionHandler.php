<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Command\UpdateCollection;

use Slink\Collection\Domain\Exception\CollectionAccessDeniedException;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Collection\Domain\ValueObject\CollectionDescription;
use Slink\Collection\Domain\ValueObject\CollectionName;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class UpdateCollectionHandler implements CommandHandlerInterface {
  public function __construct(
    private CollectionStoreRepositoryInterface $collectionStore,
  ) {
  }

  public function __invoke(UpdateCollectionCommand $command, string $userId): void {
    $collection = $this->collectionStore->get(ID::fromString($command->getId()));

    if (!$collection->isOwnedBy(ID::fromString($userId))) {
      throw new CollectionAccessDeniedException();
    }

    $name = $command->getName() !== null
      ? CollectionName::fromString($command->getName())
      : $collection->getName();

    $description = $command->getDescription() !== null
      ? CollectionDescription::fromString($command->getDescription())
      : $collection->getDescription();

    $collection->update($name, $description);

    $this->collectionStore->store($collection);
  }
}
