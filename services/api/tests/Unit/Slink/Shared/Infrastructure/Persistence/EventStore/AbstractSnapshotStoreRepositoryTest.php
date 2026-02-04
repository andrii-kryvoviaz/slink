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

  private SnapshotRepositoryFactory $factory;
  /** @var SnapshotRepository */
  private SnapshotRepository $snapshotRepository;
  /** @var ConstructingAggregateRootRepositoryWithSnapshotting */
  private ConstructingAggregateRootRepositoryWithSnapshotting $constructingRepository; //@phpstan-ignore-line
  private TestSnapshotStoreRepository $repository;

  private function createRepository(
    ?SnapshotRepository $snapshotRepository = null,
    ?ConstructingAggregateRootRepositoryWithSnapshotting $constructingRepository = null
  ): TestSnapshotStoreRepository {
    $factory = $this->createMock(SnapshotRepositoryFactory::class);
    $snapshotRepository = $snapshotRepository ?? $this->createStub(SnapshotRepository::class);
    $constructingRepository = $constructingRepository ?? $this->createStub(ConstructingAggregateRootRepositoryWithSnapshotting::class);

    $factory
      ->expects($this->once())
      ->method('createForAggregate')
      ->with('Slink\\Image\\Domain\\Image')
      ->willReturn($constructingRepository);

    return new TestSnapshotStoreRepository(
      $factory,
      $snapshotRepository,
      50
    );
  }

  protected function setUp(): void {
    $this->factory = $this->createStub(SnapshotRepositoryFactory::class);
    $this->snapshotRepository = $this->createStub(SnapshotRepository::class);
    $this->constructingRepository = $this->createStub(ConstructingAggregateRootRepositoryWithSnapshotting::class);

    $this->factory
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
    $expectedAggregate = $this->createStub(AggregateRootWithSnapshotting::class);

    $constructingRepository = $this->createMock(ConstructingAggregateRootRepositoryWithSnapshotting::class);
    $constructingRepository
      ->expects($this->once())
      ->method('retrieveFromSnapshot')
      ->with($aggregateId)
      ->willReturn($expectedAggregate);

    $repository = $this->createRepository(constructingRepository: $constructingRepository);

    $result = $repository->retrieve($aggregateId);

    $this->assertSame($expectedAggregate, $result);
  }

  #[Test]
  public function itPersistsAggregateWithoutSnapshot(): void {
    $aggregateRoot = $this->createStub(AggregateRootWithSnapshotting::class);
    $aggregateRoot->method('aggregateRootVersion')->willReturn(10);

    $constructingRepository = $this->createMock(ConstructingAggregateRootRepositoryWithSnapshotting::class);
    $constructingRepository
      ->expects($this->once())
      ->method('persist')
      ->with($aggregateRoot);

    $constructingRepository
      ->expects($this->never())
      ->method('storeSnapshot');

    $repository = $this->createRepository(constructingRepository: $constructingRepository);
    $repository->persist($aggregateRoot);
  }

  #[Test]
  public function itCreatesSnapshotWhenVersionReachesFrequency(): void {
    $aggregateId = ID::generate();
    $aggregateRoot = $this->createStub(AggregateRootWithSnapshotting::class);
    $aggregateRoot->method('aggregateRootVersion')->willReturn(50);
    $aggregateRoot->method('aggregateRootId')->willReturn($aggregateId);

    $snapshotRepository = $this->createMock(SnapshotRepository::class);
    $snapshotRepository
      ->expects($this->once())
      ->method('retrieve')
      ->with($aggregateId)
      ->willReturn(null);

    $constructingRepository = $this->createMock(ConstructingAggregateRootRepositoryWithSnapshotting::class);
    $constructingRepository
      ->expects($this->once())
      ->method('persist')
      ->with($aggregateRoot);

    $constructingRepository
      ->expects($this->once())
      ->method('storeSnapshot')
      ->with($aggregateRoot);

    $repository = $this->createRepository(snapshotRepository: $snapshotRepository, constructingRepository: $constructingRepository);
    $repository->persist($aggregateRoot);
  }

  #[Test]
  public function itCreatesSnapshotOnMultipleOfFrequency(): void {
    $aggregateId = ID::generate();
    $aggregateRoot = $this->createStub(AggregateRootWithSnapshotting::class);
    $aggregateRoot->method('aggregateRootVersion')->willReturn(100);
    $aggregateRoot->method('aggregateRootId')->willReturn($aggregateId);

    $existingSnapshot = new Snapshot($aggregateId, 50, []);
    $snapshotRepository = $this->createMock(SnapshotRepository::class);
    $snapshotRepository
      ->expects($this->once())
      ->method('retrieve')
      ->with($aggregateId)
      ->willReturn($existingSnapshot);

    $constructingRepository = $this->createMock(ConstructingAggregateRootRepositoryWithSnapshotting::class);
    $constructingRepository
      ->expects($this->once())
      ->method('persist')
      ->with($aggregateRoot);

    $constructingRepository
      ->expects($this->once())
      ->method('storeSnapshot')
      ->with($aggregateRoot);

    $repository = $this->createRepository(snapshotRepository: $snapshotRepository, constructingRepository: $constructingRepository);
    $repository->persist($aggregateRoot);
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