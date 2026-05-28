<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot\Step;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\Persistence\ManagerRegistry;
use Slink\Shared\Application\Boot\BootCategory;
use Slink\Shared\Application\Boot\BootContext;
use Slink\Shared\Application\Boot\BootResult;
use Slink\Shared\Application\Boot\BootStepInterface;
use Slink\Shared\Application\Boot\SubCommandRunner;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 100)]
final readonly class DatabaseProvisionStep implements BootStepInterface {
  private const CONNECTIONS = ['event_store', 'read_model'];

  public function __construct(
    private ManagerRegistry $registry,
    private SubCommandRunner $subCommandRunner,
  ) {}

  public function label(): string {
    return 'database provisioning';
  }

  public function category(): BootCategory {
    return BootCategory::Boot;
  }

  public function run(BootContext $context): BootResult {
    $created = [];
    $sqlite = 0;

    foreach (self::CONNECTIONS as $connectionName) {
      /** @var Connection $connection */
      $connection = $this->registry->getConnection($connectionName);

      if ($connection->getDatabasePlatform() instanceof SQLitePlatform) {
        $sqlite++;
        continue;
      }

      $result = $this->subCommandRunner->run('doctrine:database:create', [
        '--connection' => $connectionName,
        '--if-not-exists' => true,
      ]);

      if (!$result->isSuccessful()) {
        return BootResult::fail(sprintf('failed to provision %s', $connectionName), $result->output);
      }

      $created[] = $connectionName;
    }

    if ($created === []) {
      return BootResult::upToDate(sprintf('%d sqlite connection(s)', $sqlite));
    }

    return BootResult::ok(sprintf('verified %s', implode(', ', $created)));
  }
}
