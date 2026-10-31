<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot\Step;

use Slink\Shared\Application\Boot\BootCategory;
use Slink\Shared\Application\Boot\BootContext;
use Slink\Shared\Application\Boot\BootResult;
use Slink\Shared\Application\Boot\BootStepInterface;
use Doctrine\Migrations\Configuration\Connection\ConnectionRegistryConnection;
use Doctrine\Migrations\Configuration\Migration\YamlFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsTaggedItem(priority: 90)]
final readonly class EventStoreSchemaStep implements BootStepInterface {
  public function __construct(
    private ManagerRegistry $registry,
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
    $factory = DependencyFactory::fromConnection(
      new YamlFile($this->configurationPath),
      ConnectionRegistryConnection::withSimpleDefault($this->registry, 'event_store'),
    );
    $output = new BufferedOutput();
    $input = new ArrayInput(['--allow-no-migration' => true]);
    $input->setInteractive(false);
    $status = new MigrateCommand($factory)->run($input, $output);
    $text = $output->fetch();

    if ($status !== 0) {
      return BootResult::fail('migration failed', $text);
    }

    if (\str_contains($text, 'Already at the latest version') || \str_contains($text, 'No migrations to execute')) {
      return BootResult::upToDate();
    }

    return BootResult::applied('schema migrated');
  }
}
