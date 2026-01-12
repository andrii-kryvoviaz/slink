<?php

declare(strict_types=1);

namespace Unit\Slink\Settings\Domain\ValueObject\Storage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Exception\InvalidS3RegionException;
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

    $this->expectException(InvalidS3RegionException::class);

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
}
