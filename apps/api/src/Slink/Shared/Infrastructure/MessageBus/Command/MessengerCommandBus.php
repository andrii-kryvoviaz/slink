<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\MessageBus\Command;

use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\MessageBusExceptionTrait;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;
use Throwable;

final class MessengerCommandBus implements CommandBusInterface {
  use MessageBusExceptionTrait;
  
  private Envelope $envelope;

  public function __construct(private readonly MessageBusInterface $messageBus) {
  }

  /**
   * @throws Throwable
   */
  public function handle(CommandInterface|Envelope $command): void {
    try {
      $this->messageBus->dispatch($command);
    } catch (HandlerFailedException $e) {
      $this->throwException($e);
    }
  }
  
  /**
   * @throws Throwable
   */
  public function handleSync(CommandInterface|Envelope $command): mixed
  {
    $stamp = new TransportNamesStamp(['sync']);
    
    try {
      $this->envelope = $this->messageBus->dispatch($command, [$stamp]);
    } catch (HandlerFailedException $e) {
      $this->throwException($e);
    }
    
    /** @var HandledStamp $stamp */
    $stamp = $this->envelope->last(HandledStamp::class);
    
    return $stamp->getResult();
  }
  
}