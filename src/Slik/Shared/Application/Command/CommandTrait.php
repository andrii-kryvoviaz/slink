<?php

declare(strict_types=1);

namespace Slik\Shared\Application\Command;

use Symfony\Contracts\Service\Attribute\Required;

trait CommandTrait {
  private readonly CommandBusInterface $commandBus;

  #[Required]
  public function setCommandBus(CommandBusInterface $commandBus): void {
    $this->commandBus = $commandBus;
  }

  protected function handle(CommandInterface $command): void {
    $this->commandBus->handle($command);
  }
}