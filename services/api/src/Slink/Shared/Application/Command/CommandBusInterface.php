<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Command;

use Symfony\Component\Messenger\Envelope;

interface CommandBusInterface {
  public function handle(CommandInterface|Envelope $command): void;
  
  public function handleSync(CommandInterface|Envelope $command): mixed;
}