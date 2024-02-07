<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\MessageBus\Query;

use Slik\Shared\Application\Query\QueryBusInterface;
use Slik\Shared\Application\Query\QueryInterface;
use Slik\Shared\Infrastructure\MessageBus\MessageBusExceptionTrait;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Throwable;

final class MessengerQueryBus implements QueryBusInterface {
  use MessageBusExceptionTrait;
  
  private Envelope $envelope;

  public function __construct(private readonly MessageBusInterface $messageBus) {
  }

  /**
   * @throws Throwable
   */
  public function ask(QueryInterface $query): mixed {
    try {
      $this->envelope = $this->messageBus->dispatch($query);
    } catch (HandlerFailedException $e) {
      $this->throwException($e);
    }
    
    /** @var HandledStamp $stamp */
    $stamp = $this->envelope->last(HandledStamp::class);
    
    return $stamp->getResult();
  }
}