<?php

declare(strict_types=1);

namespace Tests\Integration\Console\Boot;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Shared\Application\Boot\BootStatus;
use Tests\Integration\Http\HttpTestCase;

#[Group('sc-1604')]
final class LegacyStoreUrlBootTest extends HttpTestCase {
  use RunsBootCommand;
  use SeedsLegacyEventStore;

  private const string ADMIN_ROLE = 'ROLE_ADMIN';
  private const string GRANTED = 'slink.user.domain.event.role.user_granted_role';

  protected function tearDown(): void {
    $this->removeLegacyStore();

    parent::tearDown();
  }

  /**
   * @return iterable<string, array{\Closure(string): string}>
   */
  public static function storeUrls(): iterable {
    yield 'query string' => [static fn (string $path): string => 'sqlite:///' . $path . '?cache=shared'];
    yield 'percent-encoded path' => [static fn (string $path): string => 'sqlite:///' . \str_replace('_store', '%5Fstore', $path)];
    yield 'pdo-sqlite scheme' => [static fn (string $path): string => 'pdo-sqlite:///' . $path];
    yield 'sqlite3 scheme' => [static fn (string $path): string => 'sqlite3:///' . $path];
  }

  /**
   * @param \Closure(string): string $url
   */
  #[Test]
  #[DataProvider('storeUrls')]
  public function anAdminFromTheOldStoreIsRestoredWhateverFormTheStoreUrlTakes(\Closure $url): void {
    $this->useEnv(['ADMIN_USERNAME' => 'nobody', 'ADMIN_EMAIL' => 'nobody@local.test', 'ADMIN_PASSWORD' => self::PASSWORD]);
    $userId = $this->createUser('store-admin@local.test', 'storeadmin', self::PASSWORD);
    $this->connection()->executeStatement('DELETE FROM user_to_role WHERE user_id = ?', [$userId]);
    $this->seedLegacyStore();
    $this->recordLegacyRoleEvent(self::GRANTED, $userId, self::ADMIN_ROLE, 1);
    $this->useEnv(['ES_DATABASE_URL' => $url($this->legacyStorePath())]);
    $files = $this->legacyStoreFiles();

    $report = $this->bootReport();

    self::assertSame([], $report->details(BootStatus::Fail));
    self::assertSame(1, (int) $this->connection()->fetchOne(
      'SELECT COUNT(*) FROM user_to_role WHERE user_id = ? AND role = ?',
      [$userId, self::ADMIN_ROLE],
    ));
    self::assertSame($files, $this->legacyStoreFiles());
  }
}
