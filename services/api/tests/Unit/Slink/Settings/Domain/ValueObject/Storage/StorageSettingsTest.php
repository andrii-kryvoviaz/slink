<?php

declare(strict_types=1);

namespace Unit\Slink\Settings\Domain\ValueObject\Storage;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Exception\S3BucketNotConfiguredException;
use Slink\Settings\Domain\Exception\S3CredentialsNotConfiguredException;
use Slink\Settings\Domain\Exception\S3RegionNotConfiguredException;
use Slink\Settings\Domain\Exception\SmbHostNotConfiguredException;
use Slink\Settings\Domain\Exception\SmbShareNotConfiguredException;
use Slink\Settings\Domain\ValueObject\Storage\StorageSettings;
use Slink\Shared\Domain\Enum\StorageProvider;
use Slink\Shared\Infrastructure\Encryption\EncryptionRegistry;
use Slink\Shared\Infrastructure\Encryption\EncryptionService;

final class StorageSettingsTest extends TestCase {
  protected function setUp(): void {
    EncryptionRegistry::setService(new EncryptionService('test-secret-key'));
  }

  /**
   * @return iterable<string, array{0: array<string, mixed>, 1: StorageProvider}>
   */
  public static function savableStorageConfigurations(): iterable {
    yield 'local provider keeps selection despite empty s3 block' => [
      ['provider' => 'local', 'adapter' => [
        'local' => ['dir' => 'images'],
        'smb' => null,
        's3' => ['region' => '', 'bucket' => '', 'key' => '', 'secret' => '', 'useCustomProvider' => false],
      ]],
      StorageProvider::Local,
    ];

    yield 'smb provider keeps selection despite empty s3 block' => [
      ['provider' => 'smb', 'adapter' => [
        'local' => null,
        'smb' => ['host' => 'smb.example.com', 'share' => 'uploads', 'username' => 'user', 'password' => 'pass'],
        's3' => ['region' => '', 'bucket' => '', 'key' => '', 'secret' => '', 'useCustomProvider' => false],
      ]],
      StorageProvider::SmbShare,
    ];

    yield 's3 provider with valid configuration saves' => [
      ['provider' => 's3', 'adapter' => [
        'local' => null,
        'smb' => null,
        's3' => ['region' => 'us-east-1', 'bucket' => 'my-bucket', 'key' => 'access-key', 'secret' => 'secret-key', 'useCustomProvider' => false],
      ]],
      StorageProvider::AmazonS3,
    ];

    yield 'smb provider with empty credentials still saves' => [
      ['provider' => 'smb', 'adapter' => [
        'local' => null,
        'smb' => ['host' => 'smb.example.com', 'share' => 'uploads', 'username' => '', 'password' => ''],
        's3' => null,
      ]],
      StorageProvider::SmbShare,
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  #[Test]
  #[DataProvider('savableStorageConfigurations')]
  public function itSavesTheSelectedProviderWithoutCrossValidatingOtherAdapters(array $payload, StorageProvider $expectedProvider): void {
    $settings = StorageSettings::fromPayload($payload);
    $this->assertSame($expectedProvider->value, $settings->toPayload()['provider']);
  }

  /**
   * @return iterable<string, array{0: array<string, mixed>, 1: class-string<\Throwable>}>
   */
  public static function rejectedSelectedProviderConfigurations(): iterable {
    yield 's3 selected but region missing' => [
      ['provider' => 's3', 'adapter' => [
        'local' => null,
        'smb' => null,
        's3' => ['region' => '', 'bucket' => 'my-bucket', 'key' => 'access-key', 'secret' => 'secret-key', 'useCustomProvider' => false],
      ]],
      S3RegionNotConfiguredException::class,
    ];

    yield 's3 selected but bucket missing' => [
      ['provider' => 's3', 'adapter' => [
        'local' => null,
        'smb' => null,
        's3' => ['region' => 'us-east-1', 'bucket' => '', 'key' => 'access-key', 'secret' => 'secret-key', 'useCustomProvider' => false],
      ]],
      S3BucketNotConfiguredException::class,
    ];

    yield 's3 selected but credentials missing' => [
      ['provider' => 's3', 'adapter' => [
        'local' => null,
        'smb' => null,
        's3' => ['region' => 'us-east-1', 'bucket' => 'my-bucket', 'key' => '', 'secret' => '', 'useCustomProvider' => false],
      ]],
      S3CredentialsNotConfiguredException::class,
    ];

    yield 'smb selected but host missing' => [
      ['provider' => 'smb', 'adapter' => [
        'local' => null,
        'smb' => ['host' => '', 'share' => 'uploads', 'username' => 'user', 'password' => 'pass'],
        's3' => null,
      ]],
      SmbHostNotConfiguredException::class,
    ];

    yield 'smb selected but share missing' => [
      ['provider' => 'smb', 'adapter' => [
        'local' => null,
        'smb' => ['host' => 'smb.example.com', 'share' => '', 'username' => 'user', 'password' => 'pass'],
        's3' => null,
      ]],
      SmbShareNotConfiguredException::class,
    ];
  }

  /**
   * @param array<string, mixed> $payload
   * @param class-string<\Throwable> $expectedException
   */
  #[Test]
  #[DataProvider('rejectedSelectedProviderConfigurations')]
  public function itEnforcesValidationForTheSelectedProvider(array $payload, string $expectedException): void {
    $this->expectException($expectedException);
    StorageSettings::fromPayload($payload);
  }

  #[Test]
  public function itOmitsUnselectedAdaptersFromNormalizedPayload(): void {
    $settings = StorageSettings::fromPayload([
      'provider' => 'smb',
      'adapter' => [
        'local' => null,
        'smb' => ['host' => 'smb.example.com', 'share' => 'uploads', 'username' => 'user', 'password' => 'pass'],
        's3' => ['region' => '', 'bucket' => '', 'key' => '', 'secret' => '', 'useCustomProvider' => false],
      ],
    ]);

    $normalized = $settings->toNormalizedPayload();

    // The bug: a null value here makes SettingsRepository::set() do SettingType::from('NULL') and crash.
    $this->assertNotContains(null, $normalized);
    $this->assertArrayHasKey('storage.adapter.smb.host', $normalized);
    $this->assertArrayNotHasKey('storage.adapter.s3', $normalized);
    $this->assertArrayNotHasKey('storage.adapter.local', $normalized);
  }
}
