<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\MessageBus\Query;

use Slink\Shared\Application\Query\QueryBusInterface;
use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\MessageBusExceptionTrait;
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