<?php

declare(strict_types=1);

namespace Slink\Shared\Domain;

use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use EventSauce\EventSourcing\Snapshotting\SnapshottingBehaviour;
use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\AggregateRootWithAggregates;
use EventSauce\EventSourcing\EventSourcedAggregate;
use Slink\Shared\Domain\ValueObject\ID;

/**
 * @implements AggregateRoot<ID>
 */
abstract class AbstractAggregateRoot implements AggregateRoot, AggregateRootWithSnapshotting {
  use SnapshottingBehaviour;

  use AggregateRootWithAggregates {
    AggregateRootWithAggregates::registerAggregate as registerAggregateInstance;
  }
  
  /**
   * @param AggregateRootId $aggregateRootId
   */
  protected function __construct(AggregateRootId $aggregateRootId) {
    $this->aggregateRootId = $aggregateRootId;
  }
  
  /**
   * @param EventSourcedAggregate|null $aggregate
   * @return void
   */
  protected function registerAggregate(?EventSourcedAggregate $aggregate): void {
    if($aggregate instanceof AbstractEventSourcedAggregate) {
      $aggregate->setEventRecorder($this->eventRecorder());
    }
    
    $this->registerAggregateInstance($aggregate);
  }
  
  /**
   * @return ID
   */
  #[\Override]
  public function aggregateRootId(): ID {
    return ID::fromString($this->aggregateRootId->toString());
  }
}