<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\ReadModel;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\ClassNameInflector;
use EventSauce\EventSourcing\DefaultHeadersDecorator;
use EventSauce\EventSourcing\DotSeparatedSnakeCaseInflector;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDecorator;
use EventSauce\EventSourcing\MessageDispatcher;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;

/**
 * @template TAggregate of AggregateRoot
 * @template TView of object
 */
abstract class AbstractViewBackedStore {
  private readonly MessageDecorator $decorator;
  private readonly ClassNameInflector $classNameInflector;

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

  public function __construct(private readonly MessageDispatcher $messageDispatcher) {
    $this->classNameInflector = new DotSeparatedSnakeCaseInflector();
    $this->decorator = new DefaultHeadersDecorator($this->classNameInflector);
  }

  /**
   * @return TAggregate
   * @throws NotFoundException
   */
  public function retrieve(AggregateRootId $aggregateRootId): object {
    $view = $this->findRootView(ID::fromString($aggregateRootId->toString()));

    return $this->hydrate($view);
  }

  /**
   * @param TAggregate $aggregateRoot
   */
  public function persist(object $aggregateRoot): void {
    $events = $aggregateRoot->releaseEvents();

    if ($events === []) {
      return;
    }

    $version = $aggregateRoot->aggregateRootVersion() - count($events);
    $metadata = [
      Header::AGGREGATE_ROOT_ID => $aggregateRoot->aggregateRootId(),
      Header::AGGREGATE_ROOT_TYPE => $this->classNameInflector->classNameToType(static::getAggregateRootClass()),
    ];

    $this->messageDispatcher->dispatch(...array_map(
      fn(object $event): Message => $this->decorator->decorate(
        new Message($event, $metadata + [Header::AGGREGATE_ROOT_VERSION => ++$version]),
      ),
      $events,
    ));
  }
}
