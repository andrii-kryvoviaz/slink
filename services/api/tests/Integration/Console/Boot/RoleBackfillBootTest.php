<?php

declare(strict_types=1);

namespace Tests\Integration\Console\Boot;

use PHPUnit\Framework\Attributes\DataProvider;
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
  private const string ADMIN_ROLE = 'ROLE_ADMIN';
  private const string GRANTED = 'slink.user.domain.event.role.user_granted_role';
  private const string REVOKED = 'slink.user.domain.event.role.user_revoked_role';
  private const string MEMBER_ROLE = 'ROLE_USER';

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
  #[Group('sc-1604')]
  public function theLegacyStoreIsReadButNeverWritten(): void {
    $this->seedLegacyStore();
    $this->useAdminEnv(self::USERNAME, self::EMAIL);
    $this->useEnv(['ES_DATABASE_URL' => $this->legacyStoreUrl()]);
    $userId = $this->userWithoutARoleRow();
    $secondId = $this->accountWithoutARoleRow('second-admin@local.test', 'secondadmin');
    $this->recordLegacyRoleEvent(self::GRANTED, $secondId, self::ADMIN_ROLE, 2);
    $files = $this->legacyStoreFiles();

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertCount(1, $report->details(BootStatus::Warn));
    self::assertStringContainsString('ES_DATABASE_URL', $report->details(BootStatus::Warn)[0]);
    self::assertTrue($this->holdsAdminRole($userId));
    self::assertTrue($this->holdsAdminRole($secondId));
    self::assertSame($files, $this->legacyStoreFiles());
  }

  #[Test]
  #[Group('sc-1604')]
  public function anAdminFromTheOldStoreKeepsTheRoleAfterTheAdminEmailChanged(): void {
    $this->useAdminEnv(self::USERNAME, 'changed-admin@local.test');
    $userId = $this->userWithoutARoleRow();
    $this->useLegacyStoreGranting([$userId]);
    self::assertNotSame(200, $this->apiRequest('GET', '/api/users/1', $this->login(self::USERNAME, self::PASSWORD)));

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertTrue($this->holdsAdminRole($userId));
    self::assertSame(200, $this->apiRequest('GET', '/api/users/1', $this->login(self::USERNAME, self::PASSWORD)));
    self::assertSame([], $this->pendingMigrations());
  }

  #[Test]
  #[Group('sc-1604')]
  public function anAdminFromTheOldStoreKeepsTheRoleAfterTheAdminSettingsAreRemoved(): void {
    $this->useEnv(['ADMIN_USERNAME' => 'admin', 'ADMIN_EMAIL' => '', 'ADMIN_PASSWORD' => '']);
    $userId = $this->userWithoutARoleRow();
    $this->useLegacyStoreGranting([$userId]);
    self::assertNotSame(200, $this->apiRequest('GET', '/api/users/1', $this->login(self::USERNAME, self::PASSWORD)));

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertTrue($this->holdsAdminRole($userId));
    self::assertSame(200, $this->apiRequest('GET', '/api/users/1', $this->login(self::USERNAME, self::PASSWORD)));
    self::assertSame([], $this->pendingMigrations());
  }

  #[Test]
  #[Group('sc-1604')]
  public function aSecondAdminGrantedOnTheOldReleaseKeepsTheRole(): void {
    $this->useAdminEnv(self::USERNAME, self::EMAIL);
    $userId = $this->userWithoutARoleRow();
    $secondId = $this->accountWithoutARoleRow('second-admin@local.test', 'secondadmin');
    $this->useLegacyStoreGranting([$userId, $secondId]);

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertTrue($this->holdsAdminRole($userId));
    self::assertTrue($this->holdsAdminRole($secondId));
    self::assertSame(200, $this->apiRequest('GET', '/api/users/1', $this->login('secondadmin', self::PASSWORD)));
  }

  #[Test]
  #[Group('sc-1604')]
  public function onlyAUserWhoseLastAdminEventIsAGrantRegainsTheRole(): void {
    $this->useAdminEnv('nobody', 'nobody@local.test');
    $revokedId = $this->accountWithoutARoleRow('revoked-admin@local.test', 'revokedadmin');
    $regrantedId = $this->accountWithoutARoleRow('regranted-admin@local.test', 'regrantedadmin');
    $this->seedLegacyStore();
    $this->recordLegacyRoleEvent(self::REVOKED, $revokedId, self::ADMIN_ROLE, 5);
    $this->recordLegacyRoleEvent(self::GRANTED, $revokedId, self::ADMIN_ROLE, 2);
    $this->recordLegacyRoleEvent(self::GRANTED, $revokedId, self::MEMBER_ROLE, 6);
    $this->recordLegacyRoleEvent(self::GRANTED, $regrantedId, self::ADMIN_ROLE, 7);
    $this->recordLegacyRoleEvent(self::REVOKED, $regrantedId, self::ADMIN_ROLE, 3);
    $this->useEnv(['ES_DATABASE_URL' => $this->legacyStoreUrl()]);

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertSame(0, $this->adminRoleRows($revokedId));
    self::assertTrue($this->holdsAdminRole($regrantedId));
  }

  #[Test]
  #[Group('sc-1604')]
  public function aRevokedAdminNamedByTheAdminSettingsIsNotRestored(): void {
    $this->useAdminEnv(self::USERNAME, self::EMAIL);
    $userId = $this->userWithoutARoleRow();
    $this->seedLegacyStore();
    $this->recordLegacyRoleEvent(self::GRANTED, $userId, self::ADMIN_ROLE, 1);
    $this->recordLegacyRoleEvent(self::REVOKED, $userId, self::ADMIN_ROLE, 2);
    $this->useEnv(['ES_DATABASE_URL' => $this->legacyStoreUrl()]);

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertSame(0, $this->adminRoleRows($userId));
  }

  #[Test]
  #[Group('sc-1604')]
  public function aDeletedUserGrantedInTheOldStoreGainsNoRoleRow(): void {
    $this->useAdminEnv('nobody', 'nobody@local.test');
    $adminToken = $this->bootAdmin();
    $deletedId = $this->accountWithoutARoleRow('former-admin@local.test', 'formeradmin');
    $this->softDelete($adminToken, $deletedId);
    $this->connection()->executeStatement('DELETE FROM user_to_role WHERE user_id = ?', [$deletedId]);
    $keptId = $this->accountWithoutARoleRow('kept-admin@local.test', 'keptadmin');
    $this->useLegacyStoreGranting([$deletedId, $keptId]);

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertSame(0, $this->countRowsByColumn('user_to_role', 'user_id', $deletedId));
    self::assertTrue($this->holdsAdminRole($keptId));
  }

  #[Test]
  #[Group('sc-1604')]
  public function aGrantForAnAccountMissingFromTheUserTableIsSkipped(): void {
    $this->useAdminEnv('nobody', 'nobody@local.test');
    $missingId = '0b6f2a3c-4d5e-4f60-8a71-92b3c4d5e6f7';
    $keptId = $this->accountWithoutARoleRow('kept-admin@local.test', 'keptadmin');
    $this->useLegacyStoreGranting([$missingId, $keptId]);

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertSame(0, $this->countRowsByColumn('user_to_role', 'user_id', $missingId));
    self::assertTrue($this->holdsAdminRole($keptId));
    self::assertSame([], $this->pendingMigrations());
  }

  #[Test]
  #[Group('sc-1604')]
  public function anAdminRestoredByTheStoreAndTheSettingsHoldsOneRoleRow(): void {
    $this->useAdminEnv(self::USERNAME, self::EMAIL);
    $userId = $this->userWithoutARoleRow();
    $heldId = $this->accountWithoutARoleRow('held-admin@local.test', 'heldadmin');
    $this->grantAdmin($heldId);
    $this->useLegacyStoreGranting([$userId, $heldId]);

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertSame(1, $this->adminRoleRows($userId));
    self::assertSame(1, $this->adminRoleRows($heldId));
  }

  #[Test]
  #[Group('sc-1604')]
  public function aStoreWithoutAdminEventsLeavesTheRestoreToTheAdminSettings(): void {
    $this->useAdminEnv(self::USERNAME, self::EMAIL);
    $userId = $this->userWithoutARoleRow();
    $memberId = $this->accountWithoutARoleRow('member@local.test', 'member');
    $this->seedLegacyStore();
    $this->recordLegacyRoleEvent(self::GRANTED, $memberId, self::MEMBER_ROLE, 1);
    $this->useEnv(['ES_DATABASE_URL' => $this->legacyStoreUrl()]);

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertTrue($this->holdsAdminRole($userId));
    self::assertSame(0, $this->adminRoleRows($memberId));
    self::assertSame([], $this->pendingMigrations());
  }

  /**
   * @return iterable<string, array{string}>
   */
  public static function unreadableStores(): iterable {
    yield 'not a database' => ['garbage'];
    yield 'no event table' => ['tableless'];
    yield 'undecodable payload' => ['undecodable'];
  }

  #[Test]
  #[Group('sc-1604')]
  #[DataProvider('unreadableStores')]
  public function anUnreadableStoreLeavesTheRestoreToTheAdminSettings(string $store): void {
    $this->useAdminEnv(self::USERNAME, self::EMAIL);
    $userId = $this->userWithoutARoleRow();
    $this->writeUnreadableStore($store, $userId);
    $this->useEnv(['ES_DATABASE_URL' => $this->legacyStoreUrl()]);
    $files = $this->legacyStoreFiles();

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertTrue($this->holdsAdminRole($userId));
    self::assertSame([], $this->pendingMigrations());
    self::assertSame($files, $this->legacyStoreFiles());
  }

  #[Test]
  #[Group('sc-1604')]
  public function aMissingStoreWithTheSettingSetBootsWithOnlyTheDeprecationWarning(): void {
    $this->useAdminEnv(self::USERNAME, self::EMAIL);
    $this->useEnv(['ES_DATABASE_URL' => $this->legacyStoreUrl()]);
    $userId = $this->userWithoutARoleRow();

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertCount(1, $report->details(BootStatus::Warn));
    self::assertStringContainsString('ES_DATABASE_URL', $report->details(BootStatus::Warn)[0]);
    self::assertTrue($this->holdsAdminRole($userId));
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
    return $this->accountWithoutARoleRow(self::EMAIL, $username);
  }

  private function accountWithoutARoleRow(string $email, string $username): string {
    $userId = $this->createUser($email, $username, self::PASSWORD);
    $this->connection()->executeStatement('DELETE FROM user_to_role WHERE user_id = ?', [$userId]);

    return $userId;
  }

  /**
   * @param list<string> $userIds
   */
  private function useLegacyStoreGranting(array $userIds): void {
    $this->seedLegacyStore();

    foreach ($userIds as $userId) {
      $this->recordLegacyRoleEvent(self::GRANTED, $userId, self::ADMIN_ROLE, 1);
    }

    $this->useEnv(['ES_DATABASE_URL' => $this->legacyStoreUrl()]);
  }

  private function writeUnreadableStore(string $store, string $userId): void {
    if ($store === 'garbage') {
      \file_put_contents($this->legacyStorePath(), 'this is not an sqlite database');

      return;
    }

    if ($store === 'tableless') {
      $this->legacyStore()->exec('CREATE TABLE snapshots (id INTEGER PRIMARY KEY, state TEXT NOT NULL)');

      return;
    }

    $this->seedLegacyStore();
    $statement = $this->legacyStore()->prepare(
      'INSERT INTO event_store (event_id, aggregate_root_id, version, payload) VALUES (?, ?, ?, ?)',
    );
    self::assertNotFalse($statement);
    $statement->execute(['00000000-0000-4000-8000-000000000001', $userId, 1, '{"headers": {"__event_type": "slink.user']);
  }

  private function recordLogs(): TestHandler {
    $logger = static::getContainer()->get('logger');
    self::assertInstanceOf(Logger::class, $logger);
    $handler = new TestHandler();
    $logger->pushHandler($handler);

    return $handler;
  }

  private function holdsAdminRole(string $userId): bool {
    return $this->adminRoleRows($userId) === 1;
  }

  private function adminRoleRows(string $userId): int {
    return (int) $this->connection()->fetchOne(
      'SELECT COUNT(*) FROM user_to_role WHERE user_id = ? AND role = ?',
      [$userId, self::ADMIN_ROLE],
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
