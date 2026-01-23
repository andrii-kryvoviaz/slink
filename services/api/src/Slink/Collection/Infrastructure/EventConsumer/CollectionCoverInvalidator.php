<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\EventConsumer;

use Slink\Collection\Domain\Event\CollectionItemsWereReordered;
use Slink\Collection\Domain\Event\CollectionWasDeleted;
use Slink\Collection\Domain\Event\ItemWasAddedToCollection;
use Slink\Collection\Domain\Event\ItemWasRemovedFromCollection;
use Slink\Collection\Domain\Service\CollectionCoverGeneratorInterface;
use Slink\Shared\Infrastructure\MessageBus\Event\AbstractEventConsumer;

final class CollectionCoverInvalidator extends AbstractEventConsumer {
  public function __construct(
    private readonly CollectionCoverGeneratorInterface $coverGenerator,
  ) {
  }

  public function handleItemWasAddedToCollection(ItemWasAddedToCollection $event): void {
    $this->coverGenerator->invalidateCover($event->collectionId->toString());
  }

  public function handleItemWasRemovedFromCollection(ItemWasRemovedFromCollection $event): void {
    $this->coverGenerator->invalidateCover($event->collectionId->toString());
  }

  public function handleCollectionItemsWereReordered(CollectionItemsWereReordered $event): void {
    $this->coverGenerator->invalidateCover($event->collectionId->toString());
  }

  public function handleCollectionWasDeleted(CollectionWasDeleted $event): void {
    $this->coverGenerator->invalidateCover($event->id->toString());
  }
}
