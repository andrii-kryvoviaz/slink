<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\MessageBus\Command;

use Slik\Shared\Application\Command\CommandBusInterface;
use Slik\Shared\Application\Command\CommandInterface;
use Slik\Shared\Infrastructure\MessageBus\MessageBusExceptionTrait;
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
  public function handle(CommandInterface $command): void {
    try {
      $this->messageBus->dispatch($command);
    } catch (HandlerFailedException $e) {
      $this->throwException($e);
    }
  }
}