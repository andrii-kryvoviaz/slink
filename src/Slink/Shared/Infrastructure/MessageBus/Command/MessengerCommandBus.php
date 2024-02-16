<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\MessageBus\Command;

use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\MessageBusExceptionTrait;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

final class MessengerCommandBus implements CommandBusInterface {
  use MessageBusExceptionTrait;

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
}