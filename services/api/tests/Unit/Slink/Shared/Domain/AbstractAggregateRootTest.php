<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Domain;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\EventRecorder;
use EventSauce\EventSourcing\EventSourcedAggregate;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Slink\Shared\Domain\AbstractAggregateRoot;
use Slink\Shared\Domain\AbstractEventSourcedAggregate;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\EventSourcing\AggregateRootWithAggregates;

final class AbstractAggregateRootTest extends TestCase {

  #[Test]
  public function itCanRegisterAbstractEventSourcedAggregate(): void {
    $aggregateRoot = new ConcreteAggregateRoot(ID::fromString('test-id'));
    $mockAggregate = $this->createMock(AbstractEventSourcedAggregate::class);
    $mockAggregate->expects($this->once())
      ->method('setEventRecorder');

    $aggregateRoot->testRegisterAggregate($mockAggregate);
  }

  #[Test]
  public function itCanRegisterNullAggregate(): void {
    $aggregateRoot = new ConcreteAggregateRoot(ID::fromString('test-id'));

    $aggregateRoot->testRegisterAggregate(null);

    $this->assertInstanceOf(ConcreteAggregateRoot::class, $aggregateRoot);
  }

  #[Test]
  public function itCanRegisterRegularEventSourcedAggregate(): void {
    $aggregateRoot = new ConcreteAggregateRoot(ID::fromString('test-id'));
    $stubAggregate = $this->createStub(EventSourcedAggregate::class);

    $aggregateRoot->testRegisterAggregate($stubAggregate);

    $this->assertInstanceOf(ConcreteAggregateRoot::class, $aggregateRoot);
  }

  #[Test]
  public function itDoesNotSetEventRecorderOnRegularAggregate(): void {
    $aggregateRoot = new ConcreteAggregateRoot(ID::fromString('test-id'));
    $mockAggregate = $this->createMock(EventSourcedAggregate::class);

    $mockAggregate->expects($this->never())
      ->method($this->anything());

    $aggregateRoot->testRegisterAggregate($mockAggregate);
  }

  #[Test]
  public function itImplementsAggregateRoot(): void {
    $aggregateRoot = new ConcreteAggregateRoot(ID::fromString('test-id'));

    $this->assertInstanceOf(AggregateRoot::class, $aggregateRoot);
  }

  #[Test]
  public function itIsAbstractClass(): void {
    $reflection = new ReflectionClass(AbstractAggregateRoot::class);

    $this->assertTrue($reflection->isAbstract());
  }

  #[Test]
  public function itReturnsCorrectAggregateRootId(): void {
    $id = ID::fromString('test-aggregate-id');
    $aggregateRoot = new ConcreteAggregateRoot($id);

    $returnedId = $aggregateRoot->aggregateRootId();

    $this->assertInstanceOf(ID::class, $returnedId);
    $this->assertEquals('test-aggregate-id', $returnedId->getValue());
  }

  #[Test]
  public function itSetsEventRecorderOnAbstractEventSourcedAggregate(): void {
    $aggregateRoot = new ConcreteAggregateRoot(ID::fromString('test-id'));
    $aggregate = new ConcreteEventSourcedAggregateForRootTest();

    $aggregateRoot->testRegisterAggregate($aggregate);

    $this->assertTrue($aggregate->hasEventRecorder());
  }

  #[Test]
  public function itUsesAggregateRootWithAggregatesTrait(): void {
    $reflection = new ReflectionClass(AbstractAggregateRoot::class);
    $traits = $reflection->getTraitNames();

    $this->assertContains(AggregateRootWithAggregates::class, $traits);
  }
}

class ConcreteAggregateRoot extends AbstractAggregateRoot {
  public function __construct(AggregateRootId $aggregateRootId) {
    parent::__construct($aggregateRootId);
  }

  public function testRegisterAggregate(?EventSourcedAggregate $aggregate): void {
    $this->registerAggregate($aggregate);
  }

  /**
   * @return array<string, mixed>
   */
  protected function createSnapshotState(): array {
    return ['test' => 'data'];
  }

  /**
   * @param array<string, mixed> $state
   */
  protected static function reconstituteFromSnapshotState(AggregateRootId $id, $state): AggregateRootWithSnapshotting {
    return new ConcreteAggregateRoot(ID::fromString($id->toString()));
  }
}

class ConcreteEventSourcedAggregateForRootTest extends AbstractEventSourcedAggregate {
  private bool $hasEventRecorder = false;

  public function hasEventRecorder(): bool {
    return $this->hasEventRecorder;
  }

  public function setEventRecorder(EventRecorder $eventRecorder): void {
    parent::setEventRecorder($eventRecorder);
    $this->hasEventRecorder = true;
  }
}
