<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\DataMigration;

use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class DataMigrationRunner {
  /**
   * @param iterable<DataMigrationInterface> $migrations
   */
  public function __construct(
    #[AutowireIterator('data_migration')]
    private iterable $migrations,
    private Connection $connection,
  ) {}

  public function ensureTable(): void {
    $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS "data_migration_versions" (
      version VARCHAR(255) NOT NULL PRIMARY KEY,
      description VARCHAR(255) DEFAULT NULL,
      executed_at DATETIME NOT NULL
    )');
  }

  /**
   * @return DataMigrationInterface[]
   */
  public function getPending(): array {
    $this->ensureTable();

    $executed = $this->connection
      ->executeQuery('SELECT version FROM data_migration_versions')
      ->fetchFirstColumn();

    $pending = [];

    foreach ($this->migrations as $migration) {
      $version = get_class($migration);

      if (!in_array($version, $executed, true)) {
        $pending[$version] = $migration;
      }
    }

    ksort($pending);

    return array_values($pending);
  }

  /**
   * @return list<array{version: string, executed_at: string}>
   */
  public function getExecuted(): array {
    $this->ensureTable();

    /** @var list<array{version: string, executed_at: string}> */
    return $this->connection
      ->executeQuery('SELECT version, executed_at FROM data_migration_versions ORDER BY executed_at DESC, version DESC')
      ->fetchAllAssociative();
  }

  public function execute(DataMigrationInterface $migration): void {
    $migration->up();

    $this->connection->insert('data_migration_versions', [
      'version' => get_class($migration),
      'description' => $migration->getDescription(),
      'executed_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
    ]);
  }

  public function rollback(DataMigrationInterface $migration): void {
    $migration->down();

    $this->connection->delete('data_migration_versions', [
      'version' => get_class($migration),
    ]);
  }

  public function findByVersion(string $version): ?DataMigrationInterface {
    foreach ($this->migrations as $migration) {
      if (get_class($migration) === $version) {
        return $migration;
      }
    }

    return null;
  }
}
