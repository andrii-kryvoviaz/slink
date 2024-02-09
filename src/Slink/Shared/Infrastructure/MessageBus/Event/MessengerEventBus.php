<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\MessageBus\Event;

use EventSauce\EventSourcing\Message;
use Slink\Shared\Infrastructure\MessageBus\MessageBusExceptionTrait;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Throwable;

final class MessengerEventBus {
  use MessageBusExceptionTrait;

  public function __construct(private readonly MessageBusInterface $messageBus) {
  }

  /**
   * @throws Throwable
   */
  public function handle(Message $message): void {
    try {
      $this->messageBus->dispatch($message, [
//        new AmqpStamp('#'),
      ]);
    } catch (HandlerFailedException $e) {
      $this->throwException($e);
    }
  }
}