<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Shared\Infrastructure\Persistence\EventStore;

use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use EventSauce\EventSourcing\Snapshotting\ConstructingAggregateRootRepositoryWithSnapshotting;
use EventSauce\EventSourcing\Snapshotting\Snapshot;
use EventSauce\EventSourcing\Snapshotting\SnapshotRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\EventStore\AbstractSnapshotStoreRepository;
use Slink\Shared\Infrastructure\Persistence\EventStore\SnapshotRepositoryFactory;

final class AbstractSnapshotStoreRepositoryTest extends TestCase {

  /** @var MockObject&SnapshotRepositoryFactory */
  private MockObject $factory; //@phpstan-ignore-line
  /** @var MockObject&SnapshotRepository */
  private MockObject $snapshotRepository;
  /** @var MockObject&ConstructingAggregateRootRepositoryWithSnapshotting */
  private MockObject $constructingRepository; //@phpstan-ignore-line
  private TestSnapshotStoreRepository $repository;

  protected function setUp(): void {
    $this->factory = $this->createMock(SnapshotRepositoryFactory::class);
    $this->snapshotRepository = $this->createMock(SnapshotRepository::class);
    $this->constructingRepository = $this->createMock(ConstructingAggregateRootRepositoryWithSnapshotting::class);
    
    $this->factory
      ->expects($this->once())
      ->method('createForAggregate')
      ->with('Slink\\Image\\Domain\\Image')
      ->willReturn($this->constructingRepository);

    $this->repository = new TestSnapshotStoreRepository(
      $this->factory,
      $this->snapshotRepository,
      50
    );
  }

  #[Test]
  public function itRetrievesAggregateFromSnapshot(): void {
    $aggregateId = ID::generate();
    $expectedAggregate = $this->createMock(AggregateRootWithSnapshotting::class);
    
    $this->constructingRepository
      ->expects($this->once())
      ->method('retrieveFromSnapshot')
      ->with($aggregateId)
      ->willReturn($expectedAggregate);
    
    $result = $this->repository->retrieve($aggregateId);
    
    $this->assertSame($expectedAggregate, $result);
  }

  #[Test]
  public function itPersistsAggregateWithoutSnapshot(): void {
    $aggregateRoot = $this->createMock(AggregateRootWithSnapshotting::class);
    $aggregateRoot->method('aggregateRootVersion')->willReturn(10);
    
    $this->constructingRepository
      ->expects($this->once())
      ->method('persist')
      ->with($aggregateRoot);
    
    $this->constructingRepository
      ->expects($this->never())
      ->method('storeSnapshot');
    
    $this->repository->persist($aggregateRoot);
  }

  #[Test]
  public function itCreatesSnapshotWhenVersionReachesFrequency(): void {
    $aggregateId = ID::generate();
    $aggregateRoot = $this->createMock(AggregateRootWithSnapshotting::class);
    $aggregateRoot->method('aggregateRootVersion')->willReturn(50);
    $aggregateRoot->method('aggregateRootId')->willReturn($aggregateId);
    
    $this->snapshotRepository
      ->expects($this->once())
      ->method('retrieve')
      ->with($aggregateId)
      ->willReturn(null);
    
    $this->constructingRepository
      ->expects($this->once())
      ->method('persist')
      ->with($aggregateRoot);
    
    $this->constructingRepository
      ->expects($this->once())
      ->method('storeSnapshot')
      ->with($aggregateRoot);
    
    $this->repository->persist($aggregateRoot);
  }

  #[Test]
  public function itCreatesSnapshotOnMultipleOfFrequency(): void {
    $aggregateId = ID::generate();
    $aggregateRoot = $this->createMock(AggregateRootWithSnapshotting::class);
    $aggregateRoot->method('aggregateRootVersion')->willReturn(100);
    $aggregateRoot->method('aggregateRootId')->willReturn($aggregateId);
    
    $existingSnapshot = new Snapshot($aggregateId, 50, []);
    $this->snapshotRepository
      ->expects($this->once())
      ->method('retrieve')
      ->with($aggregateId)
      ->willReturn($existingSnapshot);
    
    $this->constructingRepository
      ->expects($this->once())
      ->method('persist')
      ->with($aggregateRoot);
    
    $this->constructingRepository
      ->expects($this->once())
      ->method('storeSnapshot')
      ->with($aggregateRoot);
    
    $this->repository->persist($aggregateRoot);
  }

  #[Test]
  public function itThrowsExceptionForNonSnapshotAggregate(): void {
    $aggregateRoot = new \stdClass();
    
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Aggregate root must implement AggregateRootWithSnapshotting');
    
    $this->repository->persist($aggregateRoot);
  }
}

class TestSnapshotStoreRepository extends AbstractSnapshotStoreRepository {
  protected static function getAggregateRootClass(): string {
    return 'Slink\\Image\\Domain\\Image';
  }
}