<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Settings;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Console\Boot\RollsBackToPreviousRelease;
use Tests\Integration\Http\HttpTestCase;

#[Group('sc-1181')]
final class SettingsStoreUpgradeTest extends HttpTestCase {
  use ReloadsSettings;
  use RollsBackToPreviousRelease;

  private const string S3_SECRET = 'plain-secret-key';

  private string $adminToken = '';
  private string $collectionId = '';

  protected function setUp(): void {
    parent::setUp();

    $this->adminToken = $this->bootAdmin();
    $this->collectionId = $this->createCollection($this->adminToken);
    $this->saveSettings('image', ['maxSize' => '7M', 'compressionQuality' => 66]);
    $this->saveSettings('access', [
      'allowGuestUploads' => true,
      'allowUnauthenticatedAccess' => false,
      'requireSsl' => false,
      'requireAuthForMediaShares' => false,
      'requireAuthForCollectionShares' => false,
    ]);
    $this->saveSettings('storage', ['provider' => 's3', 'adapter' => [
      'local' => null,
      'smb' => null,
      's3' => [
        'region' => 'us-east-1',
        'bucket' => 'upgrade',
        'key' => 'plain-access-key',
        'secret' => self::S3_SECRET,
        'useCustomProvider' => false,
      ],
    ]]);

    $this->rollBackToPreviousRelease();
    $this->bootApplicationOnPreviousReleaseDatabase();
  }

  protected function tearDown(): void {
    $this->restorePreviousReleaseBackup();

    parent::tearDown();
  }

  #[Test]
  public function settingsSavedBeforeTheUpgradeLoadUnchangedFromTheirRows(): void {
    $this->forgetSettingsHistory();

    $settings = $this->reloadSettings();

    self::assertSame('7M', $settings->get('image.maxSize'));
    self::assertSame(66, $settings->get('image.compressionQuality'));
    self::assertTrue($settings->get('access.allowGuestUploads'));
    self::assertSame('s3', $settings->get('storage.provider'));
    self::assertSame(self::S3_SECRET, $this->reloadedS3Settings()->getSecret());
  }

  #[Test]
  public function aSaveAfterTheUpgradeKeepsTheOtherCategoriesAndAddsNoHistory(): void {
    $historyBeforeSave = $this->settingsHistory();

    $this->saveSettings('share', ['enableUrlShortening' => true, 'shortUrlLength' => 12]);

    $settings = $this->reloadSettings();
    self::assertSame(12, $settings->get('share.shortUrlLength'));
    self::assertSame('7M', $settings->get('image.maxSize'));
    self::assertSame($historyBeforeSave, $this->settingsHistory());
    self::assertSame(self::S3_SECRET, $this->decryptedRow('storage.adapter.s3.secret'));
  }

  #[Test]
  public function settingsWorkSideBySideWithOtherStoresAfterBoot(): void {
    $historyBeforeSave = $this->settingsHistory();

    self::assertSame(200, $this->apiRequest('GET', '/api/settings/global', $this->adminToken));
    self::assertSame(200, $this->apiRequest('GET', '/api/tags', $this->adminToken));
    self::assertSame(
      201,
      $this->apiRequest(
        'POST',
        '/api/tags',
        $this->adminToken,
        ['CONTENT_TYPE' => 'application/json'],
        \json_encode(['name' => 'after-upgrade', 'parentId' => null], JSON_THROW_ON_ERROR),
      ),
    );
    self::assertContains(
      $this->apiRequest(
        'PATCH',
        '/api/collection',
        $this->adminToken,
        ['CONTENT_TYPE' => 'application/json'],
        \json_encode(['id' => $this->collectionId, 'name' => 'renamed after upgrade', 'description' => ''], JSON_THROW_ON_ERROR),
      ),
      [200, 204],
    );
    $this->saveSettings('share', ['enableUrlShortening' => true, 'shortUrlLength' => 12]);

    self::assertSame(
      'renamed after upgrade',
      $this->connection()->fetchOne('SELECT name FROM "collection" WHERE uuid = ?', [$this->collectionId]),
    );
    self::assertSame(12, $this->reloadSettings()->get('share.shortUrlLength'));
    self::assertSame($historyBeforeSave, $this->settingsHistory());
  }
}
