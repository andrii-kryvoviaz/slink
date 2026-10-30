<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Domain;

use EventSauce\EventSourcing\EventRecorder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
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
    $eventRecorder = $this->createStub(EventRecorder::class);

    $aggregate->setEventRecorder($eventRecorder);

    $this->assertTrue($aggregate->hasEventRecorder());
  }

  #[Test]
  public function itThrowsExceptionWhenEventRecorderAlreadySet(): void {
    $aggregate = new ConcreteEventSourcedAggregateForEventTest();
    $eventRecorder = $this->createStub(EventRecorder::class);

    $aggregate->setEventRecorder($eventRecorder);

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('Event recorder already set');

    $aggregate->setEventRecorder($eventRecorder);
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
