<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\DataMigrations;

use Slink\Share\Application\Command\PublishShare\PublishShareCommand;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Infrastructure\DataMigration\DataMigrationInterface;

final class Migration20260416155324 implements DataMigrationInterface {
  public function __construct(
    private readonly ShareRepositoryInterface $shareRepository,
    private readonly CommandBusInterface $commandBus,
  ) {}

  public function up(): void {
    $errors = [];

    foreach ($this->shareRepository->findAllUnpublished() as $shareView) {
      try {
        $this->commandBus->handle(new PublishShareCommand($shareView->getId()));
      } catch (\Throwable $e) {
        $errors[] = sprintf('share %s: %s', $shareView->getId(), $e->getMessage());
      }
    }

    if ($errors !== []) {
      throw new \RuntimeException('Failed to publish some share records: ' . implode('; ', $errors));
    }
  }

  public function down(): void {}

  public function getDescription(): string {
    return 'Publish all existing share records';
  }
}
