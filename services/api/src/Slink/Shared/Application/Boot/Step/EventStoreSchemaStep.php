<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot\Step;

use Slink\Shared\Application\Boot\BootCategory;
use Slink\Shared\Application\Boot\BootContext;
use Slink\Shared\Application\Boot\BootResult;
use Slink\Shared\Application\Boot\BootStepInterface;
use Slink\Shared\Application\Boot\SubCommandRunner;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsTaggedItem(priority: 90)]
final readonly class EventStoreSchemaStep implements BootStepInterface {
  public function __construct(
    private SubCommandRunner $subCommandRunner,
    #[Autowire('%kernel.project_dir%/config/migrations/event_store.yaml')]
    private string $configurationPath,
  ) {}

  public function label(): string {
    return 'event-store schema';
  }

  public function category(): BootCategory {
    return BootCategory::Boot;
  }

  public function run(BootContext $context): BootResult {
    $result = $this->subCommandRunner->run('doctrine:migrations:migrate', [
      '--no-interaction' => true,
      '--em' => 'event_store',
      '--configuration' => $this->configurationPath,
      '--allow-no-migration' => true,
    ]);

    if (!$result->isSuccessful()) {
      return BootResult::fail('migration failed', $result->output);
    }

    if ($result->contains('Already at the latest version') || $result->contains('No migrations to execute')) {
      return BootResult::upToDate();
    }

    return BootResult::applied('schema migrated');
  }
}
