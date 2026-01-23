<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\Repository;

use Slink\Collection\Domain\Collection;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\EventStore\AbstractSnapshotStoreRepository;

final class CollectionStore extends AbstractSnapshotStoreRepository implements CollectionStoreRepositoryInterface {
  protected static function getAggregateRootClass(): string {
    return Collection::class;
  }

  public function store(Collection $collection): void {
    $this->persist($collection);
  }

  /**
   * @return Collection
   */
  public function get(ID $id): Collection {
    $collection = $this->retrieve($id);
    if (!$collection instanceof Collection) {
      throw new \RuntimeException('Expected instance of Collection, got ' . get_class($collection));
    }
    return $collection;
  }
}
