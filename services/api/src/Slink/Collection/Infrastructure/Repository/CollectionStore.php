<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use EventSauce\EventSourcing\MessageDispatcher;
use Slink\Collection\Domain\Collection;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionView;
use Slink\Shared\Domain\DataStructures\HashMap;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Persistence\EventStore\AbstractViewBackedStore;

/**
 * @extends AbstractViewBackedStore<Collection, CollectionView>
 */
final class CollectionStore extends AbstractViewBackedStore implements CollectionStoreRepositoryInterface {
  public function __construct(
    MessageDispatcher $messageDispatcher,
    private readonly CollectionRepositoryInterface $collectionRepository,
    private readonly CollectionItemRepositoryInterface $collectionItemRepository,
    private readonly EntityManagerInterface $entityManager,
  ) {
    parent::__construct($messageDispatcher);
  }

  protected static function getAggregateRootClass(): string {
    return Collection::class;
  }

  protected function findRootView(ID $id): object {
    return $this->collectionRepository->oneById($id->toString());
  }

  protected function hydrate(object $view): object {
    return Collection::fromView(
      $view,
      array_values($this->collectionItemRepository->getByCollectionId($view->getUuid())),
    );
  }

  public function store(Collection $collection): void {
    $this->persist($collection);
    $this->entityManager->flush();
  }

  public function get(ID $id): Collection {
    return $this->retrieve($id);
  }

  public function getByIds(array $ids): HashMap {
    $map = new HashMap();
    foreach ($ids as $id) {
      try {
        $map->set($id, $this->get(ID::fromString($id)));
      } catch (NotFoundException) {
      }
    }
    return $map;
  }
}
