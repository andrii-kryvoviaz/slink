<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\ReadModel\Projection;

use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Slink\Collection\Domain\Event\CollectionItemsWereReordered;
use Slink\Collection\Domain\Event\CollectionWasCreated;
use Slink\Collection\Domain\Event\CollectionWasDeleted;
use Slink\Collection\Domain\Event\CollectionWasUpdated;
use Slink\Collection\Domain\Event\ItemWasAddedToCollection;
use Slink\Collection\Domain\Event\ItemWasRemovedFromCollection;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionItemView;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionView;
use Slink\Shared\Domain\Event\EventWithEntityManager;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class CollectionProjection extends AbstractProjection {
  public function __construct(
    private readonly CollectionRepositoryInterface $collectionRepository,
    private readonly CollectionItemRepositoryInterface $collectionItemRepository,
    private readonly EntityManagerInterface $em,
  ) {
  }

  public function handleCollectionWasCreated(CollectionWasCreated $event): void {
    $eventWithEntityManager = EventWithEntityManager::decorate($event, $this->em);
    $collection = CollectionView::fromEvent($eventWithEntityManager);

    $this->collectionRepository->add($collection);
  }

  public function handleCollectionWasUpdated(CollectionWasUpdated $event): void {
    $collection = $this->collectionRepository->oneById($event->id->toString());

    $collection->updateDetails(
      $event->name->toString(),
      $event->description->toString(),
      $event->updatedAt,
    );
  }

  public function handleCollectionWasDeleted(CollectionWasDeleted $event): void {
    $collection = $this->collectionRepository->findById($event->id->toString());

    if ($collection !== null) {
      $this->collectionRepository->remove($collection);
    }
  }

  public function handleItemWasAddedToCollection(ItemWasAddedToCollection $event): void {
    $collection = $this->collectionRepository->oneById($event->collectionId->toString());

    $collectionItem = new CollectionItemView(
      Uuid::uuid4()->toString(),
      $collection,
      $event->item->getItemId()->toString(),
      $event->item->getItemType(),
      $event->item->getPosition(),
      $event->item->getAddedAt(),
    );

    $this->collectionItemRepository->add($collectionItem);
  }

  public function handleItemWasRemovedFromCollection(ItemWasRemovedFromCollection $event): void {
    $item = $this->collectionItemRepository->findByCollectionAndItemId(
      $event->collectionId->toString(),
      $event->itemId->toString(),
    );

    if ($item !== null) {
      $this->collectionItemRepository->remove($item);
    }
  }

  public function handleCollectionItemsWereReordered(CollectionItemsWereReordered $event): void {
    $position = 1.0;

    foreach ($event->orderedItemIds as $itemId) {
      $item = $this->collectionItemRepository->findByCollectionAndItemId(
        $event->collectionId->toString(),
        $itemId,
      );

      if ($item !== null) {
        $item->setPosition($position);
        $position += 1.0;
      }
    }
  }
}
