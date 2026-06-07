<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service;

use Slink\Collection\Application\Command\AddItemToCollection\AddItemToCollectionCommand;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class CollectionMembershipAssigner {
  public function __construct(
    private CommandBusInterface $commandBus,
  ) {
  }

  /**
   * @param array<string> $collectionIds
   */
  public function assign(ID $imageId, array $collectionIds, ?ID $userId): void {
    if ($userId === null) {
      return;
    }

    $context = ['userId' => $userId->toString()];

    foreach ($collectionIds as $collectionId) {
      $this->commandBus->handleSync((new AddItemToCollectionCommand(
        collectionId: $collectionId,
        itemId: $imageId->toString(),
      ))->withContext($context));
    }
  }
}
