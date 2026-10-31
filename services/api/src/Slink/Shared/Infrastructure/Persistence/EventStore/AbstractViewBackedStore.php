<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\EventStore;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\EventSourcedAggregateRootRepository;
use EventSauce\EventSourcing\MessageDispatcher;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;

/**
 * @template TAggregate of AggregateRoot
 * @template TView of object
 * @extends EventSourcedAggregateRootRepository<TAggregate>
 */
abstract class AbstractViewBackedStore extends EventSourcedAggregateRootRepository {
  /**
   * @return class-string<TAggregate>
   */
  abstract protected static function getAggregateRootClass(): string;

  /**
   * @return TView
   * @throws NotFoundException
   */
  abstract protected function findRootView(ID $id): object;

  /**
   * @param TView $view
   * @return TAggregate
   */
  abstract protected function hydrate(object $view): object;

  public function __construct(MessageDispatcher $messageDispatcher) {
    parent::__construct(static::getAggregateRootClass(), new NoOpMessageRepository(), $messageDispatcher);
  }

  /**
   * @return TAggregate
   * @throws NotFoundException
   */
  #[\Override]
  public function retrieve(AggregateRootId $aggregateRootId): object {
    $view = $this->findRootView(ID::fromString($aggregateRootId->toString()));

    return $this->hydrate($view);
  }
}
