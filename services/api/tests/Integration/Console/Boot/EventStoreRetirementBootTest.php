<?php

declare(strict_types=1);

namespace Tests\Integration\Console\Boot;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Shared\Application\Boot\BootStatus;
use Slink\Shared\Application\Boot\Step\EventStoreSchemaStep;
use Slink\Shared\Infrastructure\DataMigration\DataMigrationRunner;
use Symfony\Component\Filesystem\Filesystem;
use Tests\Integration\Http\HttpTestCase;

#[Group('sc-1183')]
final class EventStoreRetirementBootTest extends HttpTestCase {
  use RollsBackToPreviousRelease;
  use SeedsLegacyEventStore;

  private const string ADMIN_USERNAME = 'freshadmin';

  private ?string $freshInstallDirectory = null;

  protected function tearDown(): void {
    $this->restorePreviousReleaseBackup();
    $this->removeLegacyStore();

    parent::tearDown();

    if ($this->freshInstallDirectory !== null) {
      new Filesystem()->remove($this->freshInstallDirectory);
    }
  }

  #[Test]
  public function theDeprecatedSettingIsReportedOnceAndNamed(): void {
    $this->seedLegacyStore();
    $this->useEnv(['ES_DATABASE_URL' => $this->legacyStoreUrl()]);

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertSame(BootStatus::Warn, $report->resultOf(EventStoreSchemaStep::class)?->status);
    self::assertCount(1, $report->details(BootStatus::Warn));
    self::assertStringContainsString('ES_DATABASE_URL', $report->details(BootStatus::Warn)[0]);
    self::assertStringContainsString('deprecated', $report->details(BootStatus::Warn)[0]);
    self::assertStringContainsString('ignored', $report->details(BootStatus::Warn)[0]);
  }

  #[Test]
  public function theStepIsSkippedWhenTheSettingIsUnset(): void {
    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertSame(BootStatus::Skipped, $report->resultOf(EventStoreSchemaStep::class)?->status);
    self::assertSame([], $report->details(BootStatus::Warn));
  }

  #[Test]
  public function aFreshInstallWithOnlyTheMainDatabaseBootsQuietlyAndServesWrites(): void {
    $this->startFreshInstall();

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertSame([], $report->details(BootStatus::Warn));
    self::assertSame(['slink.db'], $this->freshInstallFiles());
    self::assertSame([], $this->pendingMigrations());
    self::assertTrue($this->connection()->createSchemaManager()->tablesExist(['messenger_messages']));

    $this->createUser('fresh-owner@local.test', 'freshowner', self::PASSWORD);
    $token = $this->login('freshowner', self::PASSWORD);
    $collectionId = $this->createCollection($token);

    self::assertSame(1, $this->countRowsByColumn('collection', 'uuid', $collectionId));
    self::assertSame(204, $this->deleteCollection($token, $collectionId));
    self::assertSame(0, $this->countRowsByColumn('collection', 'uuid', $collectionId));
  }

  #[Test]
  public function aFreshInstallCreatesItsAdminAndStaysQuietOnTheNextBoot(): void {
    $this->startFreshInstall();
    $this->useEnv([
      'ADMIN_EMAIL' => 'fresh-admin@local.test',
      'ADMIN_USERNAME' => self::ADMIN_USERNAME,
      'ADMIN_PASSWORD' => self::PASSWORD,
    ]);

    $first = $this->bootReport();
    $second = $this->bootReport();

    self::assertSame([], $first->details(BootStatus::Fail));
    self::assertSame([], $first->details(BootStatus::Warn));
    self::assertSame([], $second->details(BootStatus::Fail));
    self::assertSame([], $second->details(BootStatus::Warn));
    self::assertSame([], $this->pendingMigrations());
    self::assertSame(200, $this->apiRequest('GET', '/api/users/1', $this->login(self::ADMIN_USERNAME, self::PASSWORD)));
  }

  #[Test]
  public function aPreviousReleaseDatabaseGainsTheFailedQueueAndTheOldStoreKeepsItsBytes(): void {
    $this->seedLegacyStore();
    $this->useEnv(['ES_DATABASE_URL' => $this->legacyStoreUrl()]);
    $this->createUser('upgrade-owner@local.test', 'upgradeowner', self::PASSWORD);
    $token = $this->login('upgradeowner', self::PASSWORD);
    $collectionId = $this->createCollection($token);
    $this->rollBackToPreviousRelease();
    self::assertFalse($this->connection()->createSchemaManager()->tablesExist(['messenger_messages']));
    $files = $this->legacyStoreFiles();

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertCount(1, $report->details(BootStatus::Warn));
    self::assertStringContainsString('ES_DATABASE_URL', $report->details(BootStatus::Warn)[0]);
    self::assertTrue($this->connection()->createSchemaManager()->tablesExist(['messenger_messages']));
    self::assertSame('0', (string) $this->connection()->fetchOne('SELECT COUNT(*) FROM messenger_messages'));
    self::assertSame(200, $this->apiRequest('GET', '/api/user', $token));
    self::assertSame(204, $this->deleteCollection($token, $collectionId));
    self::assertSame(0, $this->countRowsByColumn('collection', 'uuid', $collectionId));
    self::assertSame($files, $this->legacyStoreFiles());

    $next = $this->bootReport();

    self::assertSame([], $next->details(BootStatus::Fail));
    self::assertCount(1, $next->details(BootStatus::Warn));
    self::assertSame($files, $this->legacyStoreFiles());
  }

  private function startFreshInstall(): void {
    $this->freshInstallDirectory = \sys_get_temp_dir() . '/slink-fresh-install-' . \bin2hex(\random_bytes(6));
    \mkdir($this->freshInstallDirectory);
    $this->useEnv(['DATABASE_URL' => 'sqlite:///' . $this->freshInstallDirectory . '/slink.db']);
  }

  /**
   * @return list<string>
   */
  private function freshInstallFiles(): array {
    self::assertNotNull($this->freshInstallDirectory);
    $files = \scandir($this->freshInstallDirectory);
    self::assertIsArray($files);

    return \array_values(\array_diff($files, ['.', '..']));
  }

  private function deleteCollection(string $token, string $collectionId): int {
    return $this->apiRequest(
      'DELETE',
      '/api/collection',
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $collectionId, 'deleteImages' => false], JSON_THROW_ON_ERROR),
    );
  }

  /**
   * @return list<class-string>
   */
  private function pendingMigrations(): array {
    $runner = static::getContainer()->get(DataMigrationRunner::class);
    self::assertInstanceOf(DataMigrationRunner::class, $runner);

    return \array_values(\array_map(static fn (object $migration): string => $migration::class, $runner->getPending()));
  }
}
