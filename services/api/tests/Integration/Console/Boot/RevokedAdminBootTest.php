<?php

declare(strict_types=1);

namespace Tests\Integration\Console\Boot;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Shared\Application\Boot\BootStatus;
use Tests\Integration\Http\HttpTestCase;

#[Group('sc-1604')]
final class RevokedAdminBootTest extends HttpTestCase {
  use RunsBootCommand;
  use SeedsLegacyEventStore;

  private const string USERNAME = 'bootadmin';
  private const string EMAIL = 'boot-admin@local.test';
  private const string ADMIN_ROLE = 'ROLE_ADMIN';
  private const string GRANTED = 'slink.user.domain.event.role.user_granted_role';
  private const string REVOKED = 'slink.user.domain.event.role.user_revoked_role';

  protected function tearDown(): void {
    $this->removeLegacyStore();

    parent::tearDown();
  }

  #[Test]
  public function aRevokedAdminNamedByTheAdminSettingsIsNotRestored(): void {
    $this->useEnv(['ADMIN_USERNAME' => self::USERNAME, 'ADMIN_EMAIL' => self::EMAIL, 'ADMIN_PASSWORD' => self::PASSWORD]);
    $userId = $this->createUser(self::EMAIL, self::USERNAME, self::PASSWORD);
    $this->connection()->executeStatement('DELETE FROM user_to_role WHERE user_id = ?', [$userId]);
    $this->seedLegacyStore();
    $this->recordLegacyRoleEvent(self::GRANTED, $userId, self::ADMIN_ROLE, 1);
    $this->recordLegacyRoleEvent(self::REVOKED, $userId, self::ADMIN_ROLE, 2);
    $this->useEnv(['ES_DATABASE_URL' => $this->legacyStoreUrl()]);

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertSame(0, (int) $this->connection()->fetchOne(
      'SELECT COUNT(*) FROM user_to_role WHERE user_id = ? AND role = ?',
      [$userId, self::ADMIN_ROLE],
    ));
  }
}
