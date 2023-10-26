<?php

declare(strict_types=1);

namespace Slik\Shared\Application\Command;

interface CommandBusInterface {
  public function handle(CommandInterface $command): void;
}