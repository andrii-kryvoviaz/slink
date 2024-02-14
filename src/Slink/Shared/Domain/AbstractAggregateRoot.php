<?php

declare(strict_types=1);

namespace Slink\Shared\Domain;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootBehaviour;
use EventSauce\EventSourcing\AggregateRootId;
use Slink\Shared\Domain\ValueObject\ID;

/**
 * @implements AggregateRoot<ID>
 */
abstract class AbstractAggregateRoot implements AggregateRoot {
  use AggregateRootBehaviour;
  
  protected function __construct(AggregateRootId $aggregateRootId) {
    $this->aggregateRootId = $aggregateRootId;
  }
  
  public function aggregateRootId(): ID {
    return ID::fromString($this->aggregateRootId->toString());
  }
}