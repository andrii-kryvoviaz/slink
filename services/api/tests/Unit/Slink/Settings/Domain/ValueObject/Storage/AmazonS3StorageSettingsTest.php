<?php

declare(strict_types=1);

namespace Unit\Slink\Settings\Domain\ValueObject\Storage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Exception\S3BucketNotConfiguredException;
use Slink\Settings\Domain\Exception\S3CredentialsNotConfiguredException;
use Slink\Settings\Domain\Exception\S3RegionNotConfiguredException;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Settings\Domain\ValueObject\Storage\AmazonS3StorageSettings;

final class AmazonS3StorageSettingsTest extends TestCase {
  #[Test]
  public function itSerializesCustomProviderSettings(): void {
    $payload = [
      'region' => 'us-east-1',
      'bucket' => 'test-bucket',
      'key' => 'access-key',
      'secret' => 'secret-key',
      'endpoint' => 'http://minio:9000',
      'useCustomProvider' => true,
      'forcePathStyle' => true,
    ];

    $settings = AmazonS3StorageSettings::fromPayload($payload);

    $this->assertSame($payload, $settings->toPayload());
    $this->assertTrue($settings->usesCustomProvider());
    $this->assertTrue($settings->isForcePathStyle());
  }

  #[Test]
  public function itDefaultsCustomProviderFlagsToFalse(): void {
    $payload = [
      'region' => 'us-east-1',
      'bucket' => 'test-bucket',
      'key' => 'access-key',
      'secret' => 'secret-key',
    ];

    $settings = AmazonS3StorageSettings::fromPayload($payload);
    $serialized = $settings->toPayload();

    $this->assertArrayHasKey('useCustomProvider', $serialized);
    $this->assertArrayHasKey('forcePathStyle', $serialized);
    $this->assertFalse($settings->usesCustomProvider());
    $this->assertFalse($settings->isForcePathStyle());
  }

  #[Test]
  public function itUsesCustomProviderWhenEndpointIsSet(): void {
    $payload = [
      'region' => 'us-east-1',
      'bucket' => 'test-bucket',
      'key' => 'access-key',
      'secret' => 'secret-key',
      'endpoint' => 'http://minio:9000',
    ];

    $settings = AmazonS3StorageSettings::fromPayload($payload);

    $this->assertTrue($settings->usesCustomProvider());
  }

  #[Test]
  public function itRequiresRegionWhenCustomProviderIsDisabled(): void {
    $payload = [
      'region' => '',
      'bucket' => 'test-bucket',
      'key' => 'access-key',
      'secret' => 'secret-key',
      'useCustomProvider' => false,
    ];

    $this->expectException(S3RegionNotConfiguredException::class);

    AmazonS3StorageSettings::fromPayload($payload);
  }

  #[Test]
  public function itAllowsEmptyRegionWhenCustomProviderIsEnabled(): void {
    $payload = [
      'region' => '',
      'bucket' => 'test-bucket',
      'key' => 'access-key',
      'secret' => 'secret-key',
      'useCustomProvider' => true,
    ];

    $settings = AmazonS3StorageSettings::fromPayload($payload);

    $this->assertSame('', $settings->getRegion());
  }

  #[Test]
  public function itThrowsExceptionWhenBucketIsEmpty(): void {
    $payload = [
      'region' => 'us-east-1',
      'bucket' => '',
      'key' => 'access-key',
      'secret' => 'secret-key',
    ];

    $this->expectException(S3BucketNotConfiguredException::class);

    AmazonS3StorageSettings::fromPayload($payload);
  }

  #[Test]
  public function itThrowsExceptionWhenKeyIsEmpty(): void {
    $payload = [
      'region' => 'us-east-1',
      'bucket' => 'test-bucket',
      'key' => '',
      'secret' => 'secret-key',
    ];

    $this->expectException(S3CredentialsNotConfiguredException::class);

    AmazonS3StorageSettings::fromPayload($payload);
  }

  #[Test]
  public function itThrowsExceptionWhenSecretIsEmpty(): void {
    $payload = [
      'region' => 'us-east-1',
      'bucket' => 'test-bucket',
      'key' => 'access-key',
      'secret' => '',
    ];

    $this->expectException(S3CredentialsNotConfiguredException::class);

    AmazonS3StorageSettings::fromPayload($payload);
  }

  #[Test]
  public function itCreatesSettingsFromConfigurationProvider(): void {
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->willReturnMap([
        ['storage.adapter.s3.region', 'us-west-2'],
        ['storage.adapter.s3.bucket', 'my-bucket'],
        ['storage.adapter.s3.key', 'my-key'],
        ['storage.adapter.s3.secret', 'my-secret'],
        ['storage.adapter.s3.endpoint', 'http://custom:9000'],
        ['storage.adapter.s3.useCustomProvider', true],
        ['storage.adapter.s3.forcePathStyle', true],
      ]);

    $settings = AmazonS3StorageSettings::fromConfig($configProvider);

    $this->assertSame('us-west-2', $settings->getRegion());
    $this->assertSame('my-bucket', $settings->getBucket());
    $this->assertSame('my-key', $settings->getKey());
    $this->assertSame('my-secret', $settings->getSecret());
    $this->assertSame('http://custom:9000', $settings->getEndpoint());
    $this->assertTrue($settings->usesCustomProvider());
    $this->assertTrue($settings->isForcePathStyle());
  }

  #[Test]
  public function itValidatesConfigurationProviderValues(): void {
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->willReturnMap([
        ['storage.adapter.s3.region', ''],
        ['storage.adapter.s3.bucket', 'my-bucket'],
        ['storage.adapter.s3.key', 'my-key'],
        ['storage.adapter.s3.secret', 'my-secret'],
        ['storage.adapter.s3.endpoint', null],
        ['storage.adapter.s3.useCustomProvider', false],
        ['storage.adapter.s3.forcePathStyle', false],
      ]);

    $this->expectException(S3RegionNotConfiguredException::class);

    AmazonS3StorageSettings::fromConfig($configProvider);
  }

  #[Test]
  public function itBuildsClientConfigForAwsProvider(): void {
    $payload = [
      'region' => 'us-east-1',
      'bucket' => 'test-bucket',
      'key' => 'access-key',
      'secret' => 'secret-key',
      'useCustomProvider' => false,
    ];

    $settings = AmazonS3StorageSettings::fromPayload($payload);
    $config = $settings->toClientConfig();

    $this->assertSame('latest', $config['version']);
    $this->assertSame('us-east-1', $config['region']);
    $this->assertSame('access-key', $config['credentials']['key']);
    $this->assertSame('secret-key', $config['credentials']['secret']);
    $this->assertArrayNotHasKey('endpoint', $config);
    $this->assertArrayNotHasKey('use_path_style_endpoint', $config);
  }

  #[Test]
  public function itBuildsClientConfigForCustomProvider(): void {
    $payload = [
      'region' => 'custom-region',
      'bucket' => 'test-bucket',
      'key' => 'access-key',
      'secret' => 'secret-key',
      'endpoint' => 'http://minio:9000',
      'useCustomProvider' => true,
      'forcePathStyle' => true,
    ];

    $settings = AmazonS3StorageSettings::fromPayload($payload);
    $config = $settings->toClientConfig();

    $this->assertSame('custom-region', $config['region']);
    $this->assertSame('http://minio:9000', $config['endpoint']);
    $this->assertTrue($config['use_path_style_endpoint']);
  }

  #[Test]
  public function itUsesAutoRegionForCustomProviderWithEmptyRegion(): void {
    $payload = [
      'region' => '',
      'bucket' => 'test-bucket',
      'key' => 'access-key',
      'secret' => 'secret-key',
      'endpoint' => 'http://minio:9000',
      'useCustomProvider' => true,
    ];

    $settings = AmazonS3StorageSettings::fromPayload($payload);
    $config = $settings->toClientConfig();

    $this->assertSame('auto', $config['region']);
  }
}
