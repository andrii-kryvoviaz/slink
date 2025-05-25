<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Command;

use Symfony\Component\Messenger\Envelope;
use Symfony\Contracts\Service\Attribute\Required;

trait CommandTrait {
  private readonly CommandBusInterface $commandBus;

  #[Required]
  public function setCommandBus(CommandBusInterface $commandBus): void {
    $this->commandBus = $commandBus;
  }

  protected function handle(CommandInterface|Envelope $command): void {
    $this->commandBus->handle($command);
  }
  
  protected function handleSync(CommandInterface|Envelope $command): mixed {
    return $this->commandBus->handleSync($command);
  }
}