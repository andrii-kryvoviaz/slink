<?php

declare(strict_types=1);

namespace Tests\Integration\Console\Boot;

use Doctrine\ORM\EntityManagerInterface;
use Slink\Shared\Infrastructure\DataMigration\DataMigrationRunner;

trait RollsBackToPreviousRelease {
  use RunsBootCommand;

  private ?string $previousSchemaBackup = null;

  private function rollBackToPreviousRelease(): void {
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);
    self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
    $entityManager->clear();

    $database = $this->databasePath();
    $this->previousSchemaBackup = $database . '.before-version-rollback';
    \copy($database, $this->previousSchemaBackup);

    foreach ($this->tablesWithVersionColumn() as $table) {
      $this->connection()->executeStatement(\sprintf('ALTER TABLE "%s" DROP COLUMN version', $table));
    }

    $this->connection()->executeStatement(
      <<<'SQL'
        DELETE FROM doctrine_migration_versions
        WHERE version = (SELECT MIN(version) FROM doctrine_migration_versions WHERE version > ?)
        SQL,
      ['Slink\\Shared\\Infrastructure\\Persistence\\Doctrine\\Migrations\\Version20260725154530'],
    );
  }

  private function bootApplicationOnPreviousReleaseDatabase(): void {
    $dataMigrations = $this->createStub(DataMigrationRunner::class);
    $dataMigrations->method('getPending')->willReturn([]);
    static::getContainer()->set(DataMigrationRunner::class, $dataMigrations);

    [$status, $output] = $this->bootResult();

    self::assertSame(0, $status, $output);
  }

  private function restorePreviousReleaseBackup(): void {
    if ($this->previousSchemaBackup === null) {
      return;
    }

    $database = $this->databasePath();
    $this->connection()->close();
    \rename($this->previousSchemaBackup, $database);
  }

  private function databasePath(): string {
    $path = $this->connection()->getParams()['path'] ?? null;
    self::assertIsString($path);

    return $path;
  }

  /**
   * @return list<string>
   */
  private function tablesWithVersionColumn(): array {
    /** @var list<string> $tables */
    $tables = $this->connection()->fetchFirstColumn(
      <<<'SQL'
        SELECT t.name
        FROM sqlite_master t, pragma_table_info(t.name) c
        WHERE t.type = 'table' AND c.name = 'version' AND t.name NOT LIKE 'doctrine_migration%'
        ORDER BY t.name
        SQL,
    );

    return $tables;
  }
}
