<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Settings;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Media\Domain\Enum\MediaFormat;
use Slink\Settings\Domain\Enum\ConfigurationProvider;
use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Settings\Domain\Repository\SettingsRepositoryInterface;
use Slink\Settings\Infrastructure\Provider\ConfigurationProviderLocator;
use Tests\Integration\Http\HttpTestCase;

#[Group('sc-1181')]
final class SettingsStoreRoundTripTest extends HttpTestCase {
  use ReloadsSettings;

  private const string S3_KEY = 'plain-access-key';
  private const string S3_SECRET = 'plain-secret-key';

  /**
   * @var list<string>
   */
  private const array UNTOUCHED_CATEGORY_KEYS = [
    'storage.provider',
    'user.allowRegistration',
    'user.localAuthEnabled',
    'user.password.minLength',
    'share.enableUrlShortening',
  ];

  /**
   * @return iterable<string, array{string, array<string, mixed>, array<string, mixed>}>
   */
  public static function everyCategoryProvider(): iterable {
    yield 'local storage with its other adapters null' => [
      'storage',
      ['provider' => 'local', 'adapter' => ['local' => ['dir' => 'images'], 'smb' => null, 's3' => null]],
      ['storage.provider' => 'local', 'storage.adapter.local.dir' => 'images'],
    ];

    yield 'user with a nested password policy' => [
      'user',
      [
        'allowRegistration' => false,
        'approvalRequired' => true,
        'localAuthEnabled' => true,
        'password' => ['minLength' => 11, 'requirements' => 3],
      ],
      [
        'user.allowRegistration' => false,
        'user.approvalRequired' => true,
        'user.password.minLength' => 11,
        'user.password.requirements' => 3,
      ],
    ];

    yield 'image' => [
      'image',
      [
        'maxSize' => '7M',
        'allowedFormats' => MediaFormat::Png->bit() | MediaFormat::Jpeg->bit(),
        'stripExifMetadata' => false,
        'compressionQuality' => 66,
        'allowOnlyPublicImages' => true,
        'enableDeduplication' => false,
      ],
      [
        'image.maxSize' => '7M',
        'image.allowedFormats' => MediaFormat::Png->bit() | MediaFormat::Jpeg->bit(),
        'image.stripExifMetadata' => false,
        'image.compressionQuality' => 66,
        'image.allowOnlyPublicImages' => true,
        'image.enableDeduplication' => false,
      ],
    ];

    yield 'access' => [
      'access',
      [
        'allowGuestUploads' => true,
        'allowUnauthenticatedAccess' => false,
        'requireSsl' => false,
        'requireAuthForMediaShares' => true,
        'requireAuthForCollectionShares' => false,
      ],
      [
        'access.allowGuestUploads' => true,
        'access.allowUnauthenticatedAccess' => false,
        'access.requireAuthForMediaShares' => true,
      ],
    ];

    yield 'share' => [
      'share',
      ['enableUrlShortening' => true, 'shortUrlLength' => 12],
      ['share.enableUrlShortening' => true, 'share.shortUrlLength' => 12],
    ];

    yield 'customization with an empty logo' => [
      'customization',
      ['siteName' => 'Round trip', 'siteDescription' => 'Reloaded from rows', 'logoUrl' => ''],
      ['customization.siteName' => 'Round trip', 'customization.siteDescription' => 'Reloaded from rows'],
    ];
  }

  #[Test]
  #[Group('sc-1131')]
  public function imageThenAccessSavedThroughTheEndpointReloadWithUntouchedCategoriesOnTheirDefaults(): void {
    $token = $this->bootAdmin();

    $this->postSettings($token, 'image', ['maxSize' => '7M', 'compressionQuality' => 66]);
    $this->postSettings($token, 'access', [
      'allowGuestUploads' => true,
      'allowUnauthenticatedAccess' => false,
      'requireSsl' => false,
      'requireAuthForMediaShares' => false,
      'requireAuthForCollectionShares' => false,
    ]);

    $settings = $this->reloadSettings();
    self::assertSame('7M', $settings->get('image.maxSize'));
    self::assertSame(66, $settings->get('image.compressionQuality'));
    self::assertTrue($settings->get('access.allowGuestUploads'));

    $resolved = $this->configurationProvider();
    $fallback = $this->fallbackProvider();

    foreach (self::UNTOUCHED_CATEGORY_KEYS as $key) {
      $expected = $fallback->get($key);

      self::assertNotNull($expected, \sprintf('Configured default for "%s" must exist.', $key));
      self::assertSame($expected, $resolved->get($key), \sprintf('Untouched "%s" must resolve its default.', $key));
    }
  }

  /**
   * @param array<string, mixed> $payload
   * @param array<string, mixed> $expected
   */
  #[Test]
  #[DataProvider('everyCategoryProvider')]
  public function aSavedCategoryReloadsFromItsRowsAlone(string $category, array $payload, array $expected): void {
    $this->saveSettings($category, $payload);

    $settings = $this->reloadSettings();

    foreach ($expected as $key => $value) {
      self::assertSame($value, $settings->get($key), $key);
    }
  }

  #[Test]
  public function s3CredentialsStayEncryptedInTheirRowsAndReloadAsPlaintext(): void {
    $this->saveS3Credentials();

    $this->assertCredentialRowsHoldCiphertextOfThePlaintext();
    $s3 = $this->reloadedS3Settings();
    self::assertSame(self::S3_KEY, $s3->getKey());
    self::assertSame(self::S3_SECRET, $s3->getSecret());
  }

  #[Test]
  public function savingTheSameS3PlaintextAgainKeepsTheRowsDecryptable(): void {
    $this->saveS3Credentials();
    $this->saveS3Credentials();

    $this->assertCredentialRowsHoldCiphertextOfThePlaintext();
    self::assertSame(self::S3_SECRET, $this->reloadedS3Settings()->getSecret());
  }

  #[Test]
  public function savingTheStorageCategoryReloadedFromRowsDoesNotEncryptItTwice(): void {
    $this->saveS3Credentials();

    /** @var array<string, mixed> $reloaded */
    $reloaded = $this->reloadSettings()->get('storage');
    $this->saveSettings('storage', $reloaded);

    $this->assertCredentialRowsHoldCiphertextOfThePlaintext();
    self::assertSame(self::S3_SECRET, $this->reloadedS3Settings()->getSecret());
  }

  #[Test]
  public function savingAnotherCategoryLeavesTheS3CredentialsDecryptable(): void {
    $this->saveS3Credentials();
    $this->saveSettings('image', ['maxSize' => '7M']);

    $this->assertCredentialRowsHoldCiphertextOfThePlaintext();
    self::assertSame(self::S3_SECRET, $this->reloadedS3Settings()->getSecret());
    self::assertSame('7M', $this->reloadSettings()->get('image.maxSize'));
  }

  #[Test]
  public function theFirstCategorySavedOnAnEmptyTableReloads(): void {
    self::assertSame(0, $this->settingsRowCount());

    $this->reloadSettings();
    $this->saveSettings('share', ['enableUrlShortening' => true, 'shortUrlLength' => 12]);

    self::assertSame(12, $this->reloadSettings()->get('share.shortUrlLength'));
  }

  #[Test]
  public function aCategoryWithoutRowsStaysEmptyBesideASavedOne(): void {
    $this->saveSettings('share', ['enableUrlShortening' => true, 'shortUrlLength' => 12]);

    $settings = $this->reloadSettings();

    self::assertSame(12, $settings->get('share.shortUrlLength'));

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('Invalid Settings key');

    $settings->get('image.maxSize');
  }

  #[Test]
  public function staleRowsOfOneCategoryDoNotBlockLaterSavesAndARepairSaveSucceeds(): void {
    $this->seedRowsOfAnEarlierRelease();
    $this->saveSettings('storage', ['provider' => 's3']);

    $this->saveSettings('share', ['enableUrlShortening' => true, 'shortUrlLength' => 12]);
    self::assertSame(12, $this->reloadSettings()->get('share.shortUrlLength'));

    $this->saveS3Credentials();
    self::assertSame(self::S3_KEY, $this->reloadedS3Settings()->getKey());
  }

  #[Test]
  public function demoRowsDoNotDisturbTheCategoriesAroundThem(): void {
    $this->seedRows(['image.maxSize' => '10M'], SettingCategory::Image);
    $this->seedRows(['demo.enabled' => true, 'demo.demoUsername' => 'demo'], SettingCategory::Demo);

    self::assertSame('10M', $this->reloadSettings()->get('image.maxSize'));

    $this->saveSettings('share', ['enableUrlShortening' => true, 'shortUrlLength' => 12]);

    self::assertSame(12, $this->reloadSettings()->get('share.shortUrlLength'));
    self::assertSame('10M', $this->reloadSettings()->get('image.maxSize'));
  }

  #[Test]
  public function rowsLeftByAnEarlierReleaseHydrateEveryCategoryAndTakeANewSave(): void {
    $this->seedRowsOfAnEarlierRelease();

    $this->saveSettings('share', ['enableUrlShortening' => true, 'shortUrlLength' => 12]);

    $settings = $this->reloadSettings();
    self::assertSame('local', $settings->get('storage.provider'));
    self::assertSame(6, $settings->get('user.password.minLength'));
    self::assertSame('10M', $settings->get('image.maxSize'));
    self::assertSame(-1, $settings->get('image.allowedFormats'));
    self::assertTrue($settings->get('access.allowUnauthenticatedAccess'));
    self::assertSame('Earlier release', $settings->get('customization.siteName'));
    self::assertSame(12, $settings->get('share.shortUrlLength'));
  }

  /**
   * @param array<string, mixed> $settings
   */
  private function postSettings(string $token, string $category, array $settings): void {
    $status = $this->apiRequest(
      'POST',
      '/api/settings',
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['category' => $category, 'settings' => $settings], JSON_THROW_ON_ERROR),
    );

    self::assertContains($status, [200, 204], (string) $this->client->getResponse()->getContent());
  }

  private function saveS3Credentials(): void {
    $this->saveSettings('storage', [
      'provider' => 's3',
      'adapter' => [
        'local' => null,
        'smb' => null,
        's3' => [
          'region' => 'us-east-1',
          'bucket' => 'round-trip',
          'key' => self::S3_KEY,
          'secret' => self::S3_SECRET,
          'useCustomProvider' => false,
        ],
      ],
    ]);
  }

  private function assertCredentialRowsHoldCiphertextOfThePlaintext(): void {
    self::assertSame(self::S3_KEY, $this->decryptedRow('storage.adapter.s3.key'));
    self::assertSame(self::S3_SECRET, $this->decryptedRow('storage.adapter.s3.secret'));

    foreach ([self::S3_KEY => 'key', self::S3_SECRET => 'secret'] as $plaintext => $field) {
      $raw = $this->rawRow('storage.adapter.s3.' . $field);

      self::assertNotSame('', $raw);
      self::assertStringNotContainsString($plaintext, $raw);
    }
  }

  private function seedRowsOfAnEarlierRelease(): void {
    $this->seedRows([
      'storage.provider' => 'local',
      'storage.adapter.local.dir' => '/app/slink/images',
      'storage.adapter.s3.region' => '',
      'storage.adapter.s3.bucket' => '',
      'storage.adapter.s3.key' => '',
      'storage.adapter.s3.secret' => '',
    ], SettingCategory::Storage);
    $this->seedRows([
      'user.allowRegistration' => true,
      'user.approvalRequired' => false,
      'user.password.minLength' => 6,
      'user.password.requirements' => 0,
    ], SettingCategory::User);
    $this->seedRows([
      'image.maxSize' => '10M',
      'image.stripExifMetadata' => true,
      'image.compressionQuality' => 71,
      'image.allowOnlyPublicImages' => false,
      'image.enableDeduplication' => true,
    ], SettingCategory::Image);
    $this->seedRows([
      'access.allowGuestUploads' => false,
      'access.allowUnauthenticatedAccess' => true,
      'access.requireSsl' => false,
    ], SettingCategory::Access);
    $this->seedRows(['customization.siteName' => 'Earlier release'], SettingCategory::Customization);
  }

  /**
   * @param array<string, mixed> $rows
   */
  private function seedRows(array $rows, SettingCategory $category): void {
    /** @var SettingsRepositoryInterface $repository */
    $repository = static::getContainer()->get(SettingsRepositoryInterface::class);

    $repository->setBulk($rows, $category);
    $this->entityManager()->flush();
  }

  private function settingsRowCount(): int {
    return (int) $this->entityManager()->getConnection()->fetchOne('SELECT COUNT(*) FROM "settings"');
  }

  private function configurationProvider(): ConfigurationProviderInterface {
    /** @var ConfigurationProviderInterface $provider */
    $provider = static::getContainer()->get(ConfigurationProviderInterface::class);

    return $provider;
  }

  private function fallbackProvider(): ConfigurationProviderInterface {
    /** @var ConfigurationProviderLocator $locator */
    $locator = static::getContainer()->get(ConfigurationProviderLocator::class);

    return $locator->get(ConfigurationProvider::Default);
  }
}
