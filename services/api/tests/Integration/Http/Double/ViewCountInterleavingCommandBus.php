<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Double;

use Slink\Image\Application\Command\AddImageViewCount\AddImageViewCountCommand;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Application\Command\CommandInterface;
use Symfony\Component\Messenger\Envelope;

final class ViewCountInterleavingCommandBus implements CommandBusInterface {
  private ?\Closure $beforeViewCount = null;

  public function __construct(private readonly CommandBusInterface $commandBus) {
  }

  public function beforeNextViewCount(\Closure $write): void {
    $this->beforeViewCount = $write;
  }

  public function hasPendingWrite(): bool {
    return $this->beforeViewCount !== null;
  }

  public function handle(CommandInterface|Envelope $command): void {
    $this->interleave($command);
    $this->commandBus->handle($command);
  }

  public function handleSync(CommandInterface|Envelope $command): mixed {
    $this->interleave($command);

    return $this->commandBus->handleSync($command);
  }

  private function interleave(CommandInterface|Envelope $command): void {
    $message = $command instanceof Envelope ? $command->getMessage() : $command;

    if (!$message instanceof AddImageViewCountCommand || $this->beforeViewCount === null) {
      return;
    }

    $write = $this->beforeViewCount;
    $this->beforeViewCount = null;
    $write();
  }
}
