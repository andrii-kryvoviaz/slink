<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\EventStore;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use EventSauce\EventSourcing\Snapshotting\ConstructingAggregateRootRepositoryWithSnapshotting;
use EventSauce\EventSourcing\Snapshotting\SnapshotRepository;
use Slink\Shared\Infrastructure\Persistence\EventStore\SnapshotRepositoryFactory;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

abstract class AbstractSnapshotStoreRepository {
  
  /**
   * @return class-string<AggregateRootWithSnapshotting>
   */
  abstract protected static function getAggregateRootClass(): string;

  private readonly ConstructingAggregateRootRepositoryWithSnapshotting $repository;
  private readonly SnapshotRepository $snapshotRepository;

  public function __construct(
    SnapshotRepositoryFactory $factory,
    SnapshotRepository $snapshotRepository,
    #[Autowire(param: 'event_sauce.snapshot_frequency')]
    protected readonly int $snapshotFrequency = 50
  ) {
    $aggregateClass = static::getAggregateRootClass();
    $this->repository = $factory->createForAggregate($aggregateClass);
    $this->snapshotRepository = $snapshotRepository;
  }  public function retrieve(AggregateRootId $aggregateRootId): object {
    return $this->repository->retrieveFromSnapshot($aggregateRootId);
  }
  
  public function persist(object $aggregateRoot): void {
    if (!$aggregateRoot instanceof AggregateRootWithSnapshotting) {
      throw new \InvalidArgumentException('Aggregate root must implement AggregateRootWithSnapshotting');
    }
    
    $this->repository->persist($aggregateRoot);
    
    $currentVersion = $aggregateRoot->aggregateRootVersion();
    
    if ($this->shouldCreateSnapshot($aggregateRoot->aggregateRootId(), $currentVersion)) {
      $this->repository->storeSnapshot($aggregateRoot);
    }
  }
  
  private function shouldCreateSnapshot(AggregateRootId $aggregateRootId, int $currentVersion): bool {
    if ($currentVersion < $this->snapshotFrequency) {
      return false;
    }
    
    $latestSnapshot = $this->snapshotRepository->retrieve($aggregateRootId);

    if ($latestSnapshot === null) {
      return $currentVersion >= $this->snapshotFrequency;
    }
    
    return $currentVersion % $this->snapshotFrequency === 0;
  }
}