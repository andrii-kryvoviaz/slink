<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\EventStore;

use Doctrine\ORM\EntityManagerInterface;
use EventSauce\EventSourcing\EventSourcedAggregateRootRepository;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use EventSauce\EventSourcing\Snapshotting\ConstructingAggregateRootRepositoryWithSnapshotting;
use Slink\Shared\Infrastructure\Persistence\EventStore\Repository\DoctrineSnapshotRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class SnapshotRepositoryFactory {
  
  public function __construct(
    private readonly MessageRepository $messageRepository,
    private readonly MessageDispatcher $messageDispatcher,
    #[Autowire(service: 'doctrine.orm.event_store_entity_manager')]
    private readonly EntityManagerInterface $entityManager
  ) {}
  
  /**
   * @template T of AggregateRootWithSnapshotting
   * @param class-string<T> $aggregateRootClassName
   * @return ConstructingAggregateRootRepositoryWithSnapshotting<T>
   */
  public function createForAggregate(string $aggregateRootClassName): ConstructingAggregateRootRepositoryWithSnapshotting {
    /** @var class-string<T> $aggregateRootClassName */
    $regularRepository = new EventSourcedAggregateRootRepository(
      $aggregateRootClassName,
      $this->messageRepository,
      $this->messageDispatcher
    );
    
    $snapshotRepository = new DoctrineSnapshotRepository($this->entityManager);
    
    return new ConstructingAggregateRootRepositoryWithSnapshotting(
      $aggregateRootClassName,
      $this->messageRepository,
      $snapshotRepository,
      $regularRepository
    );
  }
}