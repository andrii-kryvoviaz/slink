<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Command\DeleteCollection;

use Slink\Collection\Domain\Enum\ItemType;
use Slink\Collection\Domain\Exception\CollectionAccessDeniedException;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Image\Application\Command\DeleteImage\DeleteImageCommand;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class DeleteCollectionHandler implements CommandHandlerInterface {
  public function __construct(
    private CollectionStoreRepositoryInterface $collectionStore,
    private CommandBusInterface $commandBus,
  ) {
  }

  public function __invoke(DeleteCollectionCommand $command, string $userId): void {
    $collection = $this->collectionStore->get(ID::fromString($command->getId()));

    if (!$collection->isOwnedBy(ID::fromString($userId))) {
      throw new CollectionAccessDeniedException();
    }

    if ($command->shouldDeleteImages()) {
      foreach ($collection->getItems()->getItems() as $item) {
        if ($item->getItemType() !== ItemType::Image) {
          continue;
        }

        $deleteCommand = new DeleteImageCommand(preserveOnDisk: false);
        $this->commandBus->handle($deleteCommand->withContext([
          'id' => $item->getItemId()->toString(),
          'userId' => $userId,
        ]));
      }
    }

    $collection->delete();

    $this->collectionStore->store($collection);
  }
}
