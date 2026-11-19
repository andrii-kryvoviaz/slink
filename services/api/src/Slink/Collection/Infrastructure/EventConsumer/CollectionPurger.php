<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\EventConsumer;

use Doctrine\ORM\EntityManagerInterface;
use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\MessageBus\Event\AbstractEventConsumer;
use Slink\User\Domain\Event\UserWasPurged;

final class CollectionPurger extends AbstractEventConsumer {
  public function __construct(
    private readonly CollectionRepositoryInterface $collectionRepository,
    private readonly CollectionStoreRepositoryInterface $collectionStore,
    private readonly EntityManagerInterface $entityManager,
  ) {
  }

  public function handleUserWasPurged(UserWasPurged $event): void {
    foreach ($this->collectionRepository->findIdsByUserId($event->id->toString()) as $collectionId) {
      $collection = $this->collectionStore->get(ID::fromString($collectionId));
      $collection->delete();

      $this->collectionStore->store($collection);
      $this->entityManager->flush();
    }
  }
}
