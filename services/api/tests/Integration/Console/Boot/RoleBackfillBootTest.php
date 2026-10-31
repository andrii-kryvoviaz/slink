<?php

declare(strict_types=1);

namespace Tests\Integration\Console\Boot;

use PHPUnit\Framework\Attributes\Group;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\Test;
use Slink\Shared\Application\Boot\BootStatus;
use Slink\Shared\Infrastructure\DataMigration\DataMigrationRunner;
use Tests\Integration\Http\HttpTestCase;

#[Group('sc-1183')]
final class RoleBackfillBootTest extends HttpTestCase {
  use RunsBootCommand;
  use SeedsLegacyEventStore;

  private const string USERNAME = 'bootadmin';
  private const string EMAIL = 'boot-admin@local.test';

  protected function tearDown(): void {
    $this->removeLegacyStore();

    parent::tearDown();
  }

  #[Test]
  public function bootRestoresTheRoleOfTheUserNamedByTheAdminSettings(): void {
    $this->useAdminEnv(self::USERNAME, self::EMAIL);
    $userId = $this->userWithoutARoleRow();

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertSame([], $report->details(BootStatus::Warn));
    self::assertTrue($this->holdsAdminRole($userId));
    self::assertSame([], $this->pendingMigrations());
  }

  #[Test]
  public function theRestoredAdminCanUseAdminEndpointsAfterSigningIn(): void {
    $this->useAdminEnv(self::USERNAME, self::EMAIL);
    $this->userWithoutARoleRow();
    self::assertNotSame(200, $this->apiRequest('GET', '/api/users/1', $this->login(self::USERNAME, self::PASSWORD)));

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertSame(200, $this->apiRequest('GET', '/api/users/1', $this->login(self::USERNAME, self::PASSWORD)));
  }

  #[Test]
  public function aUserMatchingOnlyTheUsernameOrOnlyTheEmailGainsNoRole(): void {
    $this->useAdminEnv(self::USERNAME, 'someone-else@local.test');
    $userId = $this->userWithoutARoleRow();

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertFalse($this->holdsAdminRole($userId));
    self::assertSame([], $this->pendingMigrations());
  }

  #[Test]
  public function onlyTheAccountWithTheExactUsernameAndEmailGainsTheRole(): void {
    $this->useAdminEnv(self::USERNAME, self::EMAIL);
    $exactId = $this->userWithoutARoleRow();
    $upperId = $this->createUser(\strtoupper(self::EMAIL), \strtoupper(self::USERNAME), self::PASSWORD);
    $titleId = $this->createUser(\ucfirst(self::EMAIL), \ucfirst(self::USERNAME), self::PASSWORD);
    $this->connection()->executeStatement('DELETE FROM user_to_role WHERE user_id IN (?, ?)', [$upperId, $titleId]);

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertTrue($this->holdsAdminRole($exactId));
    self::assertFalse($this->holdsAdminRole($upperId));
    self::assertFalse($this->holdsAdminRole($titleId));
  }

  #[Test]
  public function aDeletedAdminGainsNoRoleRow(): void {
    $this->useAdminEnv('backfilldeleted', 'backfill-deleted@local.test');
    $adminToken = $this->bootAdmin();
    $deletedId = $this->createUser('backfill-deleted@local.test', 'backfilldeleted', self::PASSWORD);
    $this->softDelete($adminToken, $deletedId);
    $this->connection()->executeStatement('DELETE FROM user_to_role WHERE user_id = ?', [$deletedId]);

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertSame(0, $this->countRowsByColumn('user_to_role', 'user_id', $deletedId));
  }

  #[Test]
  public function nobodyMatchingBootsQuietlyAndRecordsTheMigrationAsDone(): void {
    $this->useAdminEnv('nobody', 'nobody@local.test');
    $this->userWithoutARoleRow();

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertSame([], $report->details(BootStatus::Warn));
    self::assertSame([], $this->pendingMigrations());
  }

  #[Test]
  public function aUsernameWithCharactersThatArePersistedEscapedStillMatches(): void {
    $username = 'boot&<admin>"\'s';
    $this->useAdminEnv($username, self::EMAIL);
    $userId = $this->userWithoutARoleRow($username);

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertTrue($this->holdsAdminRole($userId));
  }

  #[Test]
  public function anInstanceWithUsersAndNoAdminLogsTheRecoveryCommand(): void {
    $this->useAdminEnv('nobody', 'nobody@local.test');
    $this->userWithoutARoleRow();
    $logs = $this->recordLogs();

    $this->bootReport();

    self::assertTrue($logs->hasWarningThatContains('slink user:grant:role'));
  }

  #[Test]
  public function aRestoredAdminLogsNoWarning(): void {
    $this->useAdminEnv(self::USERNAME, self::EMAIL);
    $this->userWithoutARoleRow();
    $logs = $this->recordLogs();

    $this->bootReport();

    self::assertFalse($logs->hasWarningThatContains('slink user:grant:role'));
  }

  #[Test]
  public function aFreshInstallWithoutUsersLogsNoWarning(): void {
    $this->useAdminEnv('nobody', 'nobody@local.test');
    $this->connection()->executeStatement('DELETE FROM user_to_role');
    $this->connection()->executeStatement('DELETE FROM "user"');
    $logs = $this->recordLogs();

    $this->bootReport();

    self::assertFalse($logs->hasWarningThatContains('slink user:grant:role'));
  }

  #[Test]
  public function theLegacyStoreIsNeverOpenedOrWritten(): void {
    $this->seedLegacyStore();
    $this->useAdminEnv(self::USERNAME, self::EMAIL);
    $this->useEnv(['ES_DATABASE_URL' => $this->legacyStoreUrl()]);
    $userId = $this->userWithoutARoleRow();
    $files = $this->legacyStoreFiles();

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertCount(1, $report->details(BootStatus::Warn));
    self::assertTrue($this->holdsAdminRole($userId));
    self::assertSame($files, $this->legacyStoreFiles());
  }

  #[Test]
  public function aMissingStoreWithTheSettingSetBootsWithOnlyTheDeprecationWarning(): void {
    $this->useAdminEnv(self::USERNAME, self::EMAIL);
    $this->useEnv(['ES_DATABASE_URL' => $this->legacyStoreUrl()]);
    $this->userWithoutARoleRow();

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertCount(1, $report->details(BootStatus::Warn));
    self::assertStringContainsString('ES_DATABASE_URL', $report->details(BootStatus::Warn)[0]);
    self::assertSame([], $this->legacyStoreFiles());
    self::assertSame([], $this->pendingMigrations());
  }

  #[Test]
  public function aRevokedRoleIsNotRestoredOnTheNextBoot(): void {
    $this->useAdminEnv(self::USERNAME, self::EMAIL);
    $userId = $this->userWithoutARoleRow();
    $this->bootReport();
    $this->connection()->executeStatement('DELETE FROM user_to_role WHERE user_id = ? AND role = ?', [$userId, 'ROLE_ADMIN']);

    $this->bootReport();

    self::assertFalse($this->holdsAdminRole($userId));
  }

  private function useAdminEnv(string $username, string $email): void {
    $this->useEnv(['ADMIN_USERNAME' => $username, 'ADMIN_EMAIL' => $email, 'ADMIN_PASSWORD' => self::PASSWORD]);
  }

  private function userWithoutARoleRow(string $username = self::USERNAME): string {
    $userId = $this->createUser(self::EMAIL, $username, self::PASSWORD);
    $this->connection()->executeStatement('DELETE FROM user_to_role WHERE user_id = ?', [$userId]);

    return $userId;
  }

  private function recordLogs(): TestHandler {
    $logger = static::getContainer()->get('logger');
    self::assertInstanceOf(Logger::class, $logger);
    $handler = new TestHandler();
    $logger->pushHandler($handler);

    return $handler;
  }

  private function holdsAdminRole(string $userId): bool {
    return (int) $this->connection()->fetchOne(
      'SELECT COUNT(*) FROM user_to_role WHERE user_id = ? AND role = ?',
      [$userId, 'ROLE_ADMIN'],
    ) === 1;
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
