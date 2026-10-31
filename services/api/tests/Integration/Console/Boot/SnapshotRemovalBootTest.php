<?php

declare(strict_types=1);

namespace Tests\Integration\Console\Boot;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ConnectionRegistry;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Image\Domain\Image;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\DataMigration\DataMigrationRunner;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Tests\Integration\Http\HttpTestCase;

#[Group('sc-1182')]
final class SnapshotRemovalBootTest extends HttpTestCase {
  use RollsBackToPreviousRelease;

  private const string SNAPSHOTTED_AGGREGATE = '11111111-2222-4333-8444-555555555555';

  private ?string $eventStoreBackup = null;

  protected function tearDown(): void {
    $this->restoreEventStoreDatabase();
    $this->restorePreviousReleaseBackup();

    parent::tearDown();
  }

  #[Test]
  public function theSnapshotFrequencyParameterIsGone(): void {
    self::assertFalse(static::getContainer()->hasParameter('event_sauce.snapshot_frequency'));
  }

  #[Test]
  public function onlyTheReadModelEntityManagerRemainsBesideBothConnections(): void {
    self::assertSame(['read_model'], $this->entityManagerNames());
    self::assertEqualsCanonicalizing(['event_store', 'read_model'], \array_values(\array_diff($this->connectionNames(), ['default'])));
  }

  #[Test]
  public function aControllerArgumentTypedAsAnAggregateIsNotResolved(): void {
    $resolver = static::getContainer()->get('argument_resolver');
    self::assertInstanceOf(ArgumentResolverInterface::class, $resolver);
    $request = new Request(attributes: ['id' => ID::generate()->toString()]);
    $controller = static fn (Image $image): Image => $image;

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('requires the "$image" argument that could not be resolved');

    $resolver->getArguments($request, $controller);
  }

  #[Test]
  public function aFreshInstallAppliesTheEventStoreMigrationsWithoutTheirEntityManager(): void {
    self::assertSame(['read_model'], $this->entityManagerNames());
    $this->setAsideEventStoreDatabase();
    $dataMigrations = $this->createStub(DataMigrationRunner::class);
    $dataMigrations->method('getPending')->willReturn([]);
    static::getContainer()->set(DataMigrationRunner::class, $dataMigrations);

    [$status, $output] = $this->bootResult();

    self::assertSame(0, $status, $output);
    self::assertTrue($this->eventStore()->createSchemaManager()->tablesExist(['event_store', 'snapshots']), $output);
    self::assertFalse($this->connection()->createSchemaManager()->tablesExist(['event_store']), $output);
  }

  #[Test]
  public function bootOnAPreviousReleaseDatabaseKeepsItsSnapshotsAndServesWrites(): void {
    self::assertSame(['read_model'], $this->entityManagerNames());
    $this->createUser('snapshot-owner@local.test', 'snapshotowner', self::PASSWORD);
    $token = $this->login('snapshotowner', self::PASSWORD);
    $collectionId = $this->createCollection($token);
    $this->eventStore()->insert('snapshots', [
      'aggregate_root_id' => self::SNAPSHOTTED_AGGREGATE,
      'aggregate_version' => 50,
      'state' => '{"kept":true}',
    ]);

    $this->rollBackToPreviousRelease();
    $this->bootApplicationOnPreviousReleaseDatabase();

    self::assertSame(
      [['aggregate_root_id' => self::SNAPSHOTTED_AGGREGATE, 'aggregate_version' => 50, 'state' => '{"kept":true}']],
      $this->eventStore()->fetchAllAssociative('SELECT aggregate_root_id, aggregate_version, state FROM snapshots'),
    );
    self::assertSame(204, $this->apiRequest(
      'DELETE',
      '/api/collection',
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $collectionId, 'deleteImages' => false], JSON_THROW_ON_ERROR),
    ));
    self::assertSame(0, $this->countRowsByColumn('collection', 'uuid', $collectionId));
  }

  /**
   * @return list<string>
   */
  private function entityManagerNames(): array {
    $registry = static::getContainer()->get('doctrine');
    self::assertInstanceOf(ManagerRegistry::class, $registry);

    return \array_keys($registry->getManagerNames());
  }

  /**
   * @return list<string>
   */
  private function connectionNames(): array {
    $registry = static::getContainer()->get('doctrine');
    self::assertInstanceOf(ConnectionRegistry::class, $registry);

    return \array_keys($registry->getConnectionNames());
  }

  private function eventStore(): Connection {
    $connection = static::getContainer()->get('doctrine.dbal.event_store_connection');
    self::assertInstanceOf(Connection::class, $connection);

    return $connection;
  }

  private function eventStorePath(): string {
    $path = $this->eventStore()->getParams()['path'] ?? null;
    self::assertIsString($path);

    return $path;
  }

  private function setAsideEventStoreDatabase(): void {
    $path = $this->eventStorePath();
    $this->eventStoreBackup = $path . '.before-fresh-install';
    $this->eventStore()->close();
    \rename($path, $this->eventStoreBackup);
  }

  private function restoreEventStoreDatabase(): void {
    if ($this->eventStoreBackup === null) {
      return;
    }

    $this->eventStore()->close();
    \rename($this->eventStoreBackup, $this->eventStorePath());
  }
}
