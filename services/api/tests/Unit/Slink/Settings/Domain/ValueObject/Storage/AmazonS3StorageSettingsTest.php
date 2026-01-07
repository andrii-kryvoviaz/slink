<?php

declare(strict_types=1);

namespace Unit\Slink\Settings\Domain\ValueObject\Storage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\ValueObject\Storage\AmazonS3StorageSettings;

final class AmazonS3StorageSettingsTest extends TestCase {
  #[Test]
  public function itSerializesForcePathStyleSetting(): void {
    $payload = [
      'region' => 'us-east-1',
      'bucket' => 'test-bucket',
      'key' => 'access-key',
      'secret' => 'secret-key',
      'endpoint' => 'http://minio:9000',
      'forcePathStyle' => true,
    ];

    $settings = AmazonS3StorageSettings::fromPayload($payload);

    $this->assertSame($payload, $settings->toPayload());
    $this->assertTrue($settings->isForcePathStyle());
  }

  #[Test]
  public function itDefaultsForcePathStyleToNull(): void {
    $payload = [
      'region' => 'us-east-1',
      'bucket' => 'test-bucket',
      'key' => 'access-key',
      'secret' => 'secret-key',
    ];

    $settings = AmazonS3StorageSettings::fromPayload($payload);
    $serialized = $settings->toPayload();

    $this->assertArrayHasKey('forcePathStyle', $serialized);
    $this->assertNull($settings->isForcePathStyle());
  }
}
