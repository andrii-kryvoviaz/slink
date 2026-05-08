<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot\Step;

use Slink\Shared\Application\Boot\BootCategory;
use Slink\Shared\Application\Boot\BootContext;
use Slink\Shared\Application\Boot\BootResult;
use Slink\Shared\Application\Boot\BootStepInterface;
use Slink\Shared\Infrastructure\DataMigration\DataMigrationRunner;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 70)]
final readonly class DataMigrationStep implements BootStepInterface {
  public function __construct(
    private DataMigrationRunner $runner,
  ) {}

  public function label(): string {
    return 'data migrations';
  }

  public function category(): BootCategory {
    return BootCategory::Boot;
  }

  public function run(BootContext $context): BootResult {
    $pending = $this->runner->getPending();

    if ($pending === []) {
      return BootResult::upToDate();
    }

    foreach ($pending as $migration) {
      $this->runner->execute($migration);
    }

    return BootResult::applied(sprintf('%d migration(s) executed', count($pending)));
  }
}
