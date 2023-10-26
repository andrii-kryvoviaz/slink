<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\MessageBus\Query;

use Slik\Shared\Application\Query\QueryBusInterface;
use Slik\Shared\Application\Query\QueryInterface;
use Slik\Shared\Infrastructure\MessageBus\MessageBusExceptionTrait;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Throwable;

final class MessengerQueryBus implements QueryBusInterface {
  use MessageBusExceptionTrait;

  public function __construct(private readonly MessageBusInterface $messageBus) {
  }

  /**
   * @throws Throwable
   */
  public function ask(QueryInterface $query): mixed {
    try {
      $envelope = $this->messageBus->dispatch($query);

      /** @var HandledStamp $stamp */
      $stamp = $envelope->last(HandledStamp::class);

      return $stamp->getResult();
    } catch (HandlerFailedException $e) {
      $this->throwException($e);
    }
  }
}