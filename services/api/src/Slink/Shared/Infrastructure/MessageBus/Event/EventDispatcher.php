<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\MessageBus\Event;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageConsumer;
use EventSauce\EventSourcing\MessageDispatcher;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class EventDispatcher implements MessageDispatcher {
  /**
   * @param iterable<MessageConsumer> $consumers
   */
  public function __construct(
    #[AutowireIterator('event_sauce.event_consumer')]
    private readonly iterable $consumers
  ) {}

  public function dispatch(Message ...$messages): void {
    foreach ($this->consumers as $consumer) {
      foreach ($messages as $message) {
        $consumer->handle($message);
      }
    }
  }
}