<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot\Step;

use Slink\Shared\Application\Boot\BootCategory;
use Slink\Shared\Application\Boot\BootContext;
use Slink\Shared\Application\Boot\BootResult;
use Slink\Shared\Application\Boot\BootStepInterface;
use Slink\Shared\Application\Boot\SubCommandRunner;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 80)]
final readonly class DoctrineSchemaStep implements BootStepInterface {
  public function __construct(
    private SubCommandRunner $subCommandRunner,
  ) {}

  public function label(): string {
    return 'read-model schema';
  }

  public function category(): BootCategory {
    return BootCategory::Boot;
  }

  public function run(BootContext $context): BootResult {
    $result = $this->subCommandRunner->run('doctrine:migrations:migrate', [
      '--no-interaction' => true,
      '--em' => 'read_model',
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
