<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Infrastructure\DataMigration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Infrastructure\DataMigration\DataMigrationInterface;
use Slink\Shared\Infrastructure\DataMigration\DataMigrationRunner;

final class DataMigrationRunnerTest extends TestCase {
  /**
   * @param list<string> $executedVersions
   */
  private function createConnectionStub(array $executedVersions = []): Connection&Stub {
    $result = $this->createStub(Result::class);
    $result->method('fetchFirstColumn')->willReturn($executedVersions);

    $connection = $this->createStub(Connection::class);
    $connection->method('executeQuery')->willReturn($result);

    return $connection;
  }

  #[Test]
  public function itCreatesTableOnEnsure(): void {
    $connection = $this->createMock(Connection::class);
    $connection->expects($this->once())
      ->method('executeStatement')
      ->with($this->stringContains('CREATE TABLE IF NOT EXISTS'));

    $runner = new DataMigrationRunner([], $connection);
    $runner->ensureTable();
  }

  #[Test]
  public function itReturnsPendingMigrations(): void {
    $migrationA = new StubMigrationA();
    $migrationB = new StubMigrationB();

    $connection = $this->createConnectionStub([StubMigrationA::class]);

    $runner = new DataMigrationRunner([$migrationA, $migrationB], $connection);
    $pending = $runner->getPending();

    $this->assertCount(1, $pending);
    $this->assertSame($migrationB, $pending[0]);
  }

  #[Test]
  public function itReturnsEmptyWhenAllExecuted(): void {
    $connection = $this->createConnectionStub([StubMigrationA::class]);

    $runner = new DataMigrationRunner([new StubMigrationA()], $connection);

    $this->assertEmpty($runner->getPending());
  }

  #[Test]
  public function itExecutesMigrationAndRecordsVersion(): void {
    $migration = new StubMigrationA();

    $connection = $this->createMock(Connection::class);
    $connection->method('transactional')
      ->willReturnCallback(fn(\Closure $cb) => $cb($connection));
    $connection->expects($this->once())
      ->method('insert')
      ->with(
        'data_migration_versions',
        $this->callback(function (array $data): bool {
          return $data['version'] === StubMigrationA::class
            && $data['description'] === 'Stub migration A'
            && isset($data['executed_at']);
        })
      );

    $runner = new DataMigrationRunner([], $connection);
    $runner->execute($migration);

    $this->assertTrue($migration->upCalled);
  }

  #[Test]
  public function itDoesNotRecordVersionWhenMigrationFails(): void {
    $migration = new FailingMigration();

    $connection = $this->createMock(Connection::class);
    $connection->method('transactional')
      ->willReturnCallback(fn(\Closure $cb) => $cb($connection));
    $connection->expects($this->never())
      ->method('insert');

    $runner = new DataMigrationRunner([], $connection);

    $this->expectException(\RuntimeException::class);

    try {
      $runner->execute($migration);
    } finally {
      $this->assertTrue($migration->upCalled);
    }
  }

  #[Test]
  public function itRollsBackMigrationAndRemovesRecord(): void {
    $migration = new StubMigrationA();

    $connection = $this->createMock(Connection::class);
    $connection->method('transactional')
      ->willReturnCallback(fn(\Closure $cb) => $cb($connection));
    $connection->expects($this->once())
      ->method('delete')
      ->with('data_migration_versions', ['version' => StubMigrationA::class]);

    $runner = new DataMigrationRunner([], $connection);
    $runner->rollback($migration);

    $this->assertTrue($migration->downCalled);
  }

  #[Test]
  public function itDoesNotRemoveRecordWhenRollbackFails(): void {
    $migration = new FailingMigration();

    $connection = $this->createMock(Connection::class);
    $connection->method('transactional')
      ->willReturnCallback(fn(\Closure $cb) => $cb($connection));
    $connection->expects($this->never())
      ->method('delete');

    $runner = new DataMigrationRunner([], $connection);

    $this->expectException(\RuntimeException::class);

    try {
      $runner->rollback($migration);
    } finally {
      $this->assertTrue($migration->downCalled);
    }
  }

  #[Test]
  public function itFindsMigrationByVersion(): void {
    $migrationA = new StubMigrationA();
    $migrationB = new StubMigrationB();

    $connection = $this->createStub(Connection::class);

    $runner = new DataMigrationRunner([$migrationA, $migrationB], $connection);

    $this->assertSame($migrationA, $runner->findByVersion(StubMigrationA::class));
    $this->assertSame($migrationB, $runner->findByVersion(StubMigrationB::class));
  }

  #[Test]
  public function itReturnsNullForUnknownVersion(): void {
    $connection = $this->createStub(Connection::class);

    $runner = new DataMigrationRunner([], $connection);

    $this->assertNull($runner->findByVersion('Nonexistent\Class'));
  }
}

class StubMigrationA implements DataMigrationInterface {
  public bool $upCalled = false;
  public bool $downCalled = false;

  public function up(): void { $this->upCalled = true; }
  public function down(): void { $this->downCalled = true; }
  public function getDescription(): string { return 'Stub migration A'; }
}

class StubMigrationB implements DataMigrationInterface {
  public bool $upCalled = false;
  public bool $downCalled = false;

  public function up(): void { $this->upCalled = true; }
  public function down(): void { $this->downCalled = true; }
  public function getDescription(): string { return 'Stub migration B'; }
}

class FailingMigration implements DataMigrationInterface {
  public bool $upCalled = false;
  public bool $downCalled = false;

  public function up(): void {
    $this->upCalled = true;
    throw new \RuntimeException('boom');
  }

  public function down(): void {
    $this->downCalled = true;
    throw new \RuntimeException('boom');
  }

  public function getDescription(): string { return 'Failing migration'; }
}
