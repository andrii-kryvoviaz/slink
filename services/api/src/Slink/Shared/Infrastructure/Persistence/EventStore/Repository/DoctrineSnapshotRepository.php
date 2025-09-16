<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\EventStore\Repository;

use Doctrine\ORM\EntityManagerInterface;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\Snapshot;
use EventSauce\EventSourcing\Snapshotting\SnapshotRepository;
use Slink\Shared\Infrastructure\Persistence\EventStore\Entity\SnapshotEntity;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class DoctrineSnapshotRepository implements SnapshotRepository {
  
  public function __construct(
    #[Autowire(service: 'doctrine.orm.event_store_entity_manager')]
    private EntityManagerInterface $entityManager
  ) {}
  
  public function persist(Snapshot $snapshot): void {
    $repository = $this->entityManager->getRepository(SnapshotEntity::class);
    
    $existingSnapshot = $repository->findOneBy([
      'aggregateRootId' => $snapshot->aggregateRootId()->toString(),
    ]);
    
    if ($existingSnapshot) {
      $existingSnapshot->updateState(
        $snapshot->state(),
        $snapshot->aggregateRootVersion()
      );
    } else {
      $snapshotEntity = new SnapshotEntity(
        $snapshot->aggregateRootId()->toString(),
        $snapshot->aggregateRootVersion(),
        $snapshot->state(),
        new \DateTimeImmutable()
      );
      
      $this->entityManager->persist($snapshotEntity);
    }
    
    $this->entityManager->flush();
  }
  
  public function retrieve(AggregateRootId $aggregateRootId): ?Snapshot {
    $repository = $this->entityManager->getRepository(SnapshotEntity::class);
    
    $snapshotEntity = $repository->findOneBy(
      ['aggregateRootId' => $aggregateRootId->toString()],
      ['aggregateVersion' => 'DESC']
    );
    
    if (!$snapshotEntity) {
      return null;
    }
    
    return new Snapshot(
      $aggregateRootId,
      max(0, $snapshotEntity->getAggregateVersion()),
      $snapshotEntity->getState()
    );
  }
}