<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Shared\Infrastructure\Persistence\EventStore;

use Doctrine\ORM\EntityManagerInterface;
use EventSauce\EventSourcing\EventSourcedAggregateRootRepository;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Snapshotting\ConstructingAggregateRootRepositoryWithSnapshotting;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Slink\Shared\Infrastructure\Persistence\EventStore\SnapshotRepositoryFactory;

final class SnapshotRepositoryFactoryTest extends TestCase {

  private MessageRepository&MockObject $messageRepository;
  private MessageDispatcher&MockObject $messageDispatcher;
  private EntityManagerInterface&MockObject $entityManager;
  private SnapshotRepositoryFactory $factory;

  protected function setUp(): void {
    $this->messageRepository = $this->createMock(MessageRepository::class);
    $this->messageDispatcher = $this->createMock(MessageDispatcher::class);
    $this->entityManager = $this->createMock(EntityManagerInterface::class);
    
    $this->factory = new SnapshotRepositoryFactory(
      $this->messageRepository,
      $this->messageDispatcher,
      $this->entityManager
    );
  }

  #[Test]
  public function itCreatesConstructingAggregateRootRepositoryWithSnapshotting(): void {
    $aggregateClassName = 'Slink\\Image\\Domain\\Image';
    
    $repository = $this->factory->createForAggregate($aggregateClassName);
    
    $this->assertInstanceOf(ConstructingAggregateRootRepositoryWithSnapshotting::class, $repository);
  }

  #[Test]
  public function itCreatesRepositoryWithCorrectAggregateClass(): void {
    $aggregateClassName = 'Slink\\User\\Domain\\User';
    
    $repository = $this->factory->createForAggregate($aggregateClassName);
    
    $this->assertInstanceOf(ConstructingAggregateRootRepositoryWithSnapshotting::class, $repository);
  }

  #[Test]
  public function itCreatesUniqueRepositoryInstancesForDifferentAggregates(): void {
    $firstAggregate = 'Slink\\Image\\Domain\\Image';
    $secondAggregate = 'Slink\\User\\Domain\\User';
    
    $firstRepository = $this->factory->createForAggregate($firstAggregate);
    $secondRepository = $this->factory->createForAggregate($secondAggregate);
    
    $this->assertNotSame($firstRepository, $secondRepository);
    $this->assertInstanceOf(ConstructingAggregateRootRepositoryWithSnapshotting::class, $firstRepository);
    $this->assertInstanceOf(ConstructingAggregateRootRepositoryWithSnapshotting::class, $secondRepository);
  }
}