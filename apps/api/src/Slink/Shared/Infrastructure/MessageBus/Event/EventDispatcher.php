<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\MessageBus\Event;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageConsumer;
use EventSauce\EventSourcing\MessageDispatcher;

final class EventDispatcher implements MessageDispatcher {
  /**
   * @var MessageConsumer[]
   */
  private array $consumers = [];

  public function addConsumer(MessageConsumer $consumer): void {
    $this->consumers[] = $consumer;
  }

  public function dispatch(Message ...$messages): void {
    foreach ($messages as $message) {
      foreach ($this->consumers as $consumer) {
        $consumer->handle($message);
      }
    }
  }
}