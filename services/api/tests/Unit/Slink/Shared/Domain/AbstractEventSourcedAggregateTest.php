<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Domain;

use EventSauce\EventSourcing\AggregateAppliesKnownEvents;
use EventSauce\EventSourcing\EventRecorder;
use EventSauce\EventSourcing\EventSourcedAggregate;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;
use Slink\Shared\Domain\AbstractEventSourcedAggregate;
use stdClass;

final class AbstractEventSourcedAggregateTest extends TestCase {

  #[Test]
  public function itCanRecordMultipleEvents(): void {
    $aggregate = new ConcreteEventSourcedAggregateForEventTest();
    $eventRecorder = $this->createMock(EventRecorder::class);
    $event1 = new stdClass();
    $event2 = new stdClass();

    $eventRecorder->expects($this->exactly(2))
      ->method('recordThat');

    $aggregate->setEventRecorder($eventRecorder);
    $aggregate->testRecordThat($event1);
    $aggregate->testRecordThat($event2);
  }

  #[Test]
  public function itHandlesDifferentEventTypes(): void {
    $aggregate = new ConcreteEventSourcedAggregateForEventTest();
    $eventRecorder = $this->createMock(EventRecorder::class);

    $objectEvent1 = new TestEvent('test1');
    $objectEvent2 = new TestEvent('test2');
    $objectEvent3 = new stdClass();

    $eventRecorder->expects($this->exactly(3))
      ->method('recordThat');

    $aggregate->setEventRecorder($eventRecorder);
    $aggregate->testRecordThat($objectEvent1);
    $aggregate->testRecordThat($objectEvent2);
    $aggregate->testRecordThat($objectEvent3);
  }

  #[Test]
  public function itImplementsEventSourcedAggregate(): void {
    $aggregate = new ConcreteEventSourcedAggregateForEventTest();

    $this->assertInstanceOf(EventSourcedAggregate::class, $aggregate);
  }

  #[Test]
  public function itIsAbstractClass(): void {
    $reflection = new ReflectionClass(AbstractEventSourcedAggregate::class);

    $this->assertTrue($reflection->isAbstract());
  }

  #[Test]
  public function itPreventsDuplicateEventRecorderSetting(): void {
    $aggregate = new ConcreteEventSourcedAggregateForEventTest();
    $eventRecorder1 = $this->createMock(EventRecorder::class);
    $eventRecorder2 = $this->createMock(EventRecorder::class);

    $aggregate->setEventRecorder($eventRecorder1);

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('Event recorder already set');

    $aggregate->setEventRecorder($eventRecorder2);
  }

  #[Test]
  public function itRecordsEvents(): void {
    $aggregate = new ConcreteEventSourcedAggregateForEventTest();
    $eventRecorder = $this->createMock(EventRecorder::class);
    $event = new stdClass();

    $eventRecorder->expects($this->once())
      ->method('recordThat')
      ->with($event);

    $aggregate->setEventRecorder($eventRecorder);
    $aggregate->testRecordThat($event);
  }

  #[Test]
  public function itSetsEventRecorder(): void {
    $aggregate = new ConcreteEventSourcedAggregateForEventTest();
    $eventRecorder = $this->createMock(EventRecorder::class);

    $aggregate->setEventRecorder($eventRecorder);

    $this->assertTrue($aggregate->hasEventRecorder());
  }

  #[Test]
  public function itThrowsExceptionWhenEventRecorderAlreadySet(): void {
    $aggregate = new ConcreteEventSourcedAggregateForEventTest();
    $eventRecorder = $this->createMock(EventRecorder::class);

    $aggregate->setEventRecorder($eventRecorder);

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('Event recorder already set');

    $aggregate->setEventRecorder($eventRecorder);
  }

  #[Test]
  public function itUsesAggregateAppliesKnownEventsTrait(): void {
    $reflection = new ReflectionClass(AbstractEventSourcedAggregate::class);
    $traits = $reflection->getTraitNames();

    $this->assertContains(AggregateAppliesKnownEvents::class, $traits);
  }
}

class ConcreteEventSourcedAggregateForEventTest extends AbstractEventSourcedAggregate {
  private bool $hasEventRecorder = false;

  public function hasEventRecorder(): bool {
    return $this->hasEventRecorder;
  }

  public function setEventRecorder(EventRecorder $eventRecorder): void {
    parent::setEventRecorder($eventRecorder);
    $this->hasEventRecorder = true;
  }

  public function testRecordThat(object $event): void {
    $this->recordThat($event);
  }
}

class TestEvent {
  public function __construct(public readonly string $data) {
  }
}
