<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Shared\Infrastructure\FileSystem\Storage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Exception\S3BucketNotConfiguredException;
use Slink\Settings\Domain\Exception\S3CredentialsNotConfiguredException;
use Slink\Settings\Domain\Exception\S3RegionNotConfiguredException;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Settings\Domain\ValueObject\Storage\AmazonS3StorageSettings;

final class AmazonS3StorageTest extends TestCase {
  #[Test]
  public function itThrowsExceptionWhenRegionIsMissingForAws(): void {
    $configProvider = $this->createConfigProvider([
      'storage.adapter.s3.region' => '',
      'storage.adapter.s3.bucket' => 'my-bucket',
      'storage.adapter.s3.key' => 'access-key',
      'storage.adapter.s3.secret' => 'secret-key',
      'storage.adapter.s3.endpoint' => null,
      'storage.adapter.s3.useCustomProvider' => false,
      'storage.adapter.s3.forcePathStyle' => false,
    ]);

    $this->expectException(S3RegionNotConfiguredException::class);

    AmazonS3StorageSettings::fromConfig($configProvider);
  }

  #[Test]
  public function itAllowsEmptyRegionForCustomProvider(): void {
    $configProvider = $this->createConfigProvider([
      'storage.adapter.s3.region' => '',
      'storage.adapter.s3.bucket' => 'my-bucket',
      'storage.adapter.s3.key' => 'access-key',
      'storage.adapter.s3.secret' => 'secret-key',
      'storage.adapter.s3.endpoint' => 'http://minio:9000',
      'storage.adapter.s3.useCustomProvider' => true,
      'storage.adapter.s3.forcePathStyle' => true,
    ]);

    $settings = AmazonS3StorageSettings::fromConfig($configProvider);

    $this->assertSame('my-bucket', $settings->getBucket());
  }

  #[Test]
  public function itThrowsExceptionWhenBucketIsMissing(): void {
    $configProvider = $this->createConfigProvider([
      'storage.adapter.s3.region' => 'us-east-1',
      'storage.adapter.s3.bucket' => '',
      'storage.adapter.s3.key' => 'access-key',
      'storage.adapter.s3.secret' => 'secret-key',
      'storage.adapter.s3.endpoint' => null,
      'storage.adapter.s3.useCustomProvider' => false,
      'storage.adapter.s3.forcePathStyle' => false,
    ]);

    $this->expectException(S3BucketNotConfiguredException::class);

    AmazonS3StorageSettings::fromConfig($configProvider);
  }

  #[Test]
  public function itThrowsExceptionWhenAccessKeyIsMissing(): void {
    $configProvider = $this->createConfigProvider([
      'storage.adapter.s3.region' => 'us-east-1',
      'storage.adapter.s3.bucket' => 'my-bucket',
      'storage.adapter.s3.key' => '',
      'storage.adapter.s3.secret' => 'secret-key',
      'storage.adapter.s3.endpoint' => null,
      'storage.adapter.s3.useCustomProvider' => false,
      'storage.adapter.s3.forcePathStyle' => false,
    ]);

    $this->expectException(S3CredentialsNotConfiguredException::class);

    AmazonS3StorageSettings::fromConfig($configProvider);
  }

  #[Test]
  public function itThrowsExceptionWhenSecretKeyIsMissing(): void {
    $configProvider = $this->createConfigProvider([
      'storage.adapter.s3.region' => 'us-east-1',
      'storage.adapter.s3.bucket' => 'my-bucket',
      'storage.adapter.s3.key' => 'access-key',
      'storage.adapter.s3.secret' => '',
      'storage.adapter.s3.endpoint' => null,
      'storage.adapter.s3.useCustomProvider' => false,
      'storage.adapter.s3.forcePathStyle' => false,
    ]);

    $this->expectException(S3CredentialsNotConfiguredException::class);

    AmazonS3StorageSettings::fromConfig($configProvider);
  }

  #[Test]
  public function itInitializesWithValidAwsConfig(): void {
    $configProvider = $this->createConfigProvider([
      'storage.adapter.s3.region' => 'us-east-1',
      'storage.adapter.s3.bucket' => 'my-bucket',
      'storage.adapter.s3.key' => 'access-key',
      'storage.adapter.s3.secret' => 'secret-key',
      'storage.adapter.s3.endpoint' => null,
      'storage.adapter.s3.useCustomProvider' => false,
      'storage.adapter.s3.forcePathStyle' => false,
    ]);

    $settings = AmazonS3StorageSettings::fromConfig($configProvider);

    $this->assertSame('us-east-1', $settings->getRegion());
    $this->assertSame('my-bucket', $settings->getBucket());
  }

  #[Test]
  public function itInitializesWithValidCustomProviderConfig(): void {
    $configProvider = $this->createConfigProvider([
      'storage.adapter.s3.region' => 'auto',
      'storage.adapter.s3.bucket' => 'my-bucket',
      'storage.adapter.s3.key' => 'access-key',
      'storage.adapter.s3.secret' => 'secret-key',
      'storage.adapter.s3.endpoint' => 'http://minio:9000',
      'storage.adapter.s3.useCustomProvider' => true,
      'storage.adapter.s3.forcePathStyle' => true,
    ]);

    $settings = AmazonS3StorageSettings::fromConfig($configProvider);

    $this->assertSame('auto', $settings->getRegion());
    $this->assertTrue($settings->usesCustomProvider());
  }

  #[Test]
  public function itAutoDetectsCustomProviderWhenEndpointIsSet(): void {
    $configProvider = $this->createConfigProvider([
      'storage.adapter.s3.region' => '',
      'storage.adapter.s3.bucket' => 'my-bucket',
      'storage.adapter.s3.key' => 'access-key',
      'storage.adapter.s3.secret' => 'secret-key',
      'storage.adapter.s3.endpoint' => 'http://minio:9000',
      'storage.adapter.s3.useCustomProvider' => null,
      'storage.adapter.s3.forcePathStyle' => false,
    ]);

    $settings = AmazonS3StorageSettings::fromConfig($configProvider);

    $this->assertTrue($settings->usesCustomProvider());
  }

  /**
   * @param array<string, mixed> $config
   */
  private function createConfigProvider(array $config): ConfigurationProviderInterface {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->willReturnCallback(fn(string $key) => $config[$key] ?? null);

    return $configProvider;
  }
}
