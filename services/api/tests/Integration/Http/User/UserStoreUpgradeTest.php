<?php

declare(strict_types=1);

namespace Tests\Integration\Http\User;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\DataMigration\DataMigrationRunner;
use Slink\Shared\Infrastructure\Persistence\Doctrine\DataMigrations\Migration20261031143201;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\UserPreferences;
use Tests\Integration\Console\Boot\RollsBackToPreviousRelease;
use Tests\Integration\Console\Boot\SeedsLegacyEventStore;
use Tests\Integration\Http\Double\StubOAuthAdapter;
use Tests\Integration\Http\HttpTestCase;

#[Group('sc-1180')]
#[Group('sc-1183')]
final class UserStoreUpgradeTest extends HttpTestCase {
  use ReloadsUsers;
  use RollsBackToPreviousRelease;
  use SeedsLegacyEventStore;

  private const string MEMBER_EMAIL = 'upgrade-member@local.test';
  private const string MEMBER_USERNAME = 'upgrademember';
  private const string NEW_PASSWORD = 'NewPassword456!';

  private string $adminToken = '';
  private string $adminId = '';
  private string $memberId = '';
  private string $deletedId = '';
  private string $purgedId = '';
  private string $namelessId = '';
  private string $memberRefreshToken = '';

  protected function setUp(): void {
    parent::setUp();

    StubOAuthAdapter::reset();
    $this->seedLegacyStore();
    $this->useEnv([
      'ES_DATABASE_URL' => $this->legacyStoreUrl(),
      'ADMIN_USERNAME' => 'adminuser',
      'ADMIN_EMAIL' => 'admin@local.test',
    ]);
    $this->adminToken = $this->bootAdmin();
    $this->adminId = $this->userIdByUsername('adminuser');
    $this->dropRoleRows($this->adminId);
    $this->memberId = $this->createUserWithRolesSubjectAndPreferences(
      self::MEMBER_EMAIL,
      self::MEMBER_USERNAME,
      'Ann & Bob',
      'subject-upgrade',
    );
    $this->memberRefreshToken = $this->authenticateWithRefreshToken(self::MEMBER_USERNAME, self::PASSWORD)['refresh_token'];
    $this->deletedId = $this->createUser('upgrade-deleted@local.test', 'upgradedeleted', self::PASSWORD);
    $this->softDelete($this->adminToken, $this->deletedId);
    $this->purgedId = $this->createUser('upgrade-purged@local.test', 'upgradepurged', self::PASSWORD);
    self::assertSame(204, $this->purgeUser($this->adminToken, $this->purgedId));
    $this->namelessId = $this->createUser('upgrade-nameless@local.test', 'upgradenameless', self::PASSWORD);
    $this->clearDisplayName($this->namelessId);

    $this->rollBackToPreviousRelease();
    $this->forgetRoleRowMigration();
    $this->assertRoleRowMigrationPending();
    [$status, $output] = $this->bootResult();
    self::assertSame(0, $status, $output);
    $this->assertRoleRowMigrationExecuted();
    $this->userEntityManager()->clear();
  }

  protected function tearDown(): void {
    StubOAuthAdapter::reset();
    $this->restorePreviousReleaseBackup();
    $this->removeLegacyStore();

    parent::tearDown();
  }

  #[Test]
  public function aUserFromThePreviousReleaseReloadsFromItsRows(): void {
    $user = $this->reloadedUser($this->memberId);

    self::assertSame(self::MEMBER_EMAIL, $this->emailOf($user));
    self::assertSame(self::MEMBER_USERNAME, $user->getUsername()?->toString());
    self::assertSame('Ann &amp; Bob', $user->getDisplayName()->toString());
    self::assertSame(UserStatus::Active, $user->getStatus());
    self::assertSame(['ROLE_ADMIN', 'ROLE_USER'], $this->sortedRolesOf($user));
    self::assertSame(['acme:subject-upgrade'], $this->subjectsOf($user));
    self::assertSame(self::PREFERENCES, $user->getPreferences()->toPayload());
  }

  #[Test]
  public function theBootAdminOfThePreviousReleaseKeepsItsRoleAfterBoot(): void {
    $adminToken = $this->login('adminuser', self::PASSWORD);

    self::assertSame(200, $this->apiRequest('GET', '/api/users/1', $adminToken));
    self::assertSame(['ROLE_ADMIN', 'ROLE_USER'], $this->sortedRolesOf($this->reloadedUser($this->adminId)));
  }

  #[Test]
  public function aRefreshTokenFromThePreviousReleaseRotatesAndTheOldOneStopsWorking(): void {
    $rotated = $this->rotatedRefreshToken($this->memberRefreshToken);

    self::assertSame(400, $this->refreshStatus($this->memberRefreshToken));
    self::assertSame(200, $this->refreshStatus($rotated));
  }

  #[Test]
  public function aSessionFromThePreviousReleaseSignsOut(): void {
    $before = $this->countRowsByColumn('refresh_token', 'user_uuid', $this->memberId);

    self::assertContains($this->signOut($this->memberRefreshToken), [200, 204]);

    self::assertSame($before - 1, $this->countRowsByColumn('refresh_token', 'user_uuid', $this->memberId));
    self::assertSame(400, $this->refreshStatus($this->memberRefreshToken));
  }

  #[Test]
  public function anExpiredTokenFromThePreviousReleaseIsRemovedAtTheNextSignIn(): void {
    $this->expireRefreshTokens($this->memberId);
    $this->userEntityManager()->clear();

    $fresh = $this->authenticateWithRefreshToken(self::MEMBER_USERNAME, self::PASSWORD);

    self::assertSame(1, $this->countRowsByColumn('refresh_token', 'user_uuid', $this->memberId));
    self::assertSame(200, $this->refreshStatus($fresh['refresh_token']));
  }

  #[Test]
  public function everyUserRequestOfThePreviousReleaseStillSucceeds(): void {
    $memberToken = $this->login(self::MEMBER_USERNAME, self::PASSWORD);

    self::assertSame(200, $this->apiRequest('GET', '/api/user', $memberToken));
    self::assertSame(200, $this->apiRequest('GET', '/api/user/preferences', $memberToken));
    self::assertSame(200, $this->apiRequest('GET', '/api/users/1', $this->adminToken));
    $this->updatePreferences($memberToken);
    self::assertContains($this->updateDisplayName($memberToken, 'Changed Name'), [200, 204]);
    self::assertContains($this->changePassword($memberToken, self::NEW_PASSWORD), [200, 204]);
    self::assertSame(200, $this->changeUserStatus($this->adminToken, $this->memberId, UserStatus::Suspended));

    self::assertSame(UserStatus::Suspended, $this->reloadedUser($this->memberId)->getStatus());
    self::assertSame('Changed Name', $this->storedDisplayName($this->memberId));
  }

  #[Test]
  public function aUserDeletedBeforeTheUpgradeReloadsAndCanBePurged(): void {
    $user = $this->reloadedUser($this->deletedId);

    self::assertNull($this->emailOf($user));
    self::assertNull($user->getUsername());
    self::assertSame(UserStatus::Deleted, $user->getStatus());
    self::assertSame(204, $this->purgeUser($this->adminToken, $this->deletedId));
    self::assertSame(0, $this->countRowsByColumn('user_preferences', 'user_id', $this->deletedId));
  }

  #[Test]
  public function aUserPurgedBeforeTheUpgradeReloadsAndCanBePurgedAgain(): void {
    $user = $this->reloadedUser($this->purgedId);

    self::assertNull($this->emailOf($user));
    self::assertNull($user->getUsername());
    self::assertSame(UserStatus::Deleted, $user->getStatus());
    self::assertSame(UserPreferences::empty()->toPayload(), $user->getPreferences()->toPayload());
    self::assertSame(204, $this->purgeUser($this->adminToken, $this->purgedId));
  }

  #[Test]
  public function aUserWithoutANameFromThePreviousReleaseKeepsNoNameThroughAWrite(): void {
    self::assertSame(200, $this->changeUserStatus($this->adminToken, $this->namelessId, UserStatus::Suspended));

    self::assertNull($this->storedDisplayName($this->namelessId));
    self::assertSame('Guest', $this->reloadedUser($this->namelessId)->getDisplayName()->toString());
  }

  #[Test]
  public function usersWorkBesideTheOtherStoresAfterBoot(): void {
    $memberToken = $this->login(self::MEMBER_USERNAME, self::PASSWORD);
    $newUserId = $this->createUser('upgrade-new@local.test', 'upgradenew', self::PASSWORD);
    $collectionId = $this->createCollection($memberToken);

    self::assertSame(201, $this->apiRequest(
      'POST',
      '/api/tags',
      $memberToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['name' => 'after-upgrade'], JSON_THROW_ON_ERROR),
    ));
    self::assertContains(
      $this->apiRequest(
        'PATCH',
        '/api/collection',
        $memberToken,
        ['CONTENT_TYPE' => 'application/json'],
        \json_encode(['id' => $collectionId, 'name' => 'renamed after upgrade', 'description' => ''], JSON_THROW_ON_ERROR),
      ),
      [200, 204],
    );
    self::assertSame(200, $this->apiRequest('GET', '/api/user', $this->login('upgradenew', self::PASSWORD)));

    self::assertSame(UserStatus::Active, $this->reloadedUser($newUserId)->getStatus());
  }

  private function userIdByUsername(string $username): string {
    return (string) $this->userEntityManager()->getConnection()
      ->fetchOne('SELECT uuid FROM "user" WHERE username = ?', [$username]);
  }

  private function dropRoleRows(string $userId): void {
    $this->userEntityManager()->getConnection()
      ->executeStatement('DELETE FROM user_to_role WHERE user_id = ?', [$userId]);
  }

  private function forgetRoleRowMigration(): void {
    $runner = static::getContainer()->get(DataMigrationRunner::class);
    self::assertInstanceOf(DataMigrationRunner::class, $runner);
    $runner->ensureTable();
    $this->userEntityManager()->getConnection()
      ->executeStatement('DELETE FROM data_migration_versions WHERE version = ?', [Migration20261031143201::class]);
  }

  /**
   * @return list<string>
   */
  private function migrationClasses(): array {
    $runner = static::getContainer()->get(DataMigrationRunner::class);
    self::assertInstanceOf(DataMigrationRunner::class, $runner);

    return \array_values(\array_map(static fn (object $migration): string => $migration::class, $runner->getPending()));
  }

  private function assertRoleRowMigrationPending(): void {
    self::assertContains(Migration20261031143201::class, $this->migrationClasses());
  }

  private function assertRoleRowMigrationExecuted(): void {
    self::assertNotContains(Migration20261031143201::class, $this->migrationClasses());
  }

  private function reloadedUser(string $userId): User {
    $this->userEntityManager()->clear();

    return $this->userStore()->get(ID::fromString($userId));
  }
}
