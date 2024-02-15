<?php

declare(strict_types=1);

namespace Slink\Shared\Domain;

use EventSauce\EventSourcing\AggregateAppliesKnownEvents;
use EventSauce\EventSourcing\EventRecorder;
use EventSauce\EventSourcing\EventSourcedAggregate;

abstract class AbstractEventSourcedAggregate implements EventSourcedAggregate {
  use AggregateAppliesKnownEvents;
  
  /**
   * @var EventRecorder
   */
  private EventRecorder $eventRecorder;
  
  /**
   * @throws \RuntimeException
   */
  public function setEventRecorder(EventRecorder $eventRecorder): void {
    if(isset($this->eventRecorder)) {
      throw new \RuntimeException('Event recorder already set');
    }
    
    $this->eventRecorder = $eventRecorder;
  }
  
  /**
   * @param object $event
   * @return void
   */
  protected function recordThat(object $event): void {
    $this->eventRecorder->recordThat($event);
  }
}