<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\Persistence\EventStore;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\EventSourcedAggregateRootRepository;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\MessageRepository;

abstract class AbstractStoreRepository extends EventSourcedAggregateRootRepository{
  /**
   * @return class-string<AggregateRoot>
   */
  abstract static function getAggregateRootClass(): string;
  
  /**
   * @param MessageRepository $messageRepository
   * @param MessageDispatcher $messageDispatcher
   */
  public function __construct(MessageRepository $messageRepository, MessageDispatcher $messageDispatcher) {
    parent::__construct(static::getAggregateRootClass(), $messageRepository, $messageDispatcher);
  }
}