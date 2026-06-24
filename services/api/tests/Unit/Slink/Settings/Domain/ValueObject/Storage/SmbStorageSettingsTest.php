<?php

declare(strict_types=1);

namespace Unit\Slink\Settings\Domain\ValueObject\Storage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Exception\SmbHostNotConfiguredException;
use Slink\Settings\Domain\Exception\SmbShareNotConfiguredException;
use Slink\Settings\Domain\ValueObject\Storage\SmbStorageSettings;
use Slink\Shared\Infrastructure\Encryption\EncryptionRegistry;
use Slink\Shared\Infrastructure\Encryption\EncryptionService;

final class SmbStorageSettingsTest extends TestCase {
  protected function setUp(): void {
    EncryptionRegistry::setService(new EncryptionService('test-secret-key'));
  }

  #[Test]
  public function itPersistsWorkgroupThroughPayloadRoundTrip(): void {
    $payload = SmbStorageSettings::fromPayload([
      'host' => 'nas.local',
      'share' => 'media',
      'username' => 'u',
      'password' => 'p',
      'workgroup' => 'CORP',
    ])->toPayload();

    $this->assertSame('CORP', $payload['workgroup'] ?? null);
  }

  #[Test]
  public function itDefaultsWorkgroupToEmptyWhenAbsent(): void {
    $payload = SmbStorageSettings::fromPayload([
      'host' => 'nas.local',
      'share' => 'media',
      'username' => 'u',
      'password' => 'p',
    ])->toPayload();

    $this->assertSame('', $payload['workgroup'] ?? null);
  }

  #[Test]
  public function itRequiresHost(): void {
    $this->expectException(SmbHostNotConfiguredException::class);

    SmbStorageSettings::fromPayload([
      'host' => '',
      'share' => 'media',
      'username' => 'u',
      'password' => 'p',
    ]);
  }

  #[Test]
  public function itRequiresHostKeyToBePresent(): void {
    $this->expectException(SmbHostNotConfiguredException::class);

    SmbStorageSettings::fromPayload([
      'share' => 'media',
      'username' => 'u',
      'password' => 'p',
    ]);
  }

  #[Test]
  public function itRequiresShare(): void {
    $this->expectException(SmbShareNotConfiguredException::class);

    SmbStorageSettings::fromPayload([
      'host' => 'nas.local',
      'share' => '',
      'username' => 'u',
      'password' => 'p',
    ]);
  }

  #[Test]
  public function itRequiresShareKeyToBePresent(): void {
    $this->expectException(SmbShareNotConfiguredException::class);

    SmbStorageSettings::fromPayload([
      'host' => 'nas.local',
      'username' => 'u',
      'password' => 'p',
    ]);
  }

  #[Test]
  public function itTrimsHostAndShare(): void {
    $settings = SmbStorageSettings::fromPayload([
      'host' => '  nas.local  ',
      'share' => '  media  ',
      'username' => 'u',
      'password' => 'p',
    ]);

    $payload = $settings->toPayload();

    $this->assertSame('nas.local', $payload['host']);
    $this->assertSame('media', $payload['share']);
  }

  #[Test]
  public function itEncryptsPasswordInPayloadAndDecryptsBackToOriginal(): void {
    $payload = SmbStorageSettings::fromPayload([
      'host' => 'nas.local',
      'share' => 'media',
      'username' => 'u',
      'password' => 'super-secret',
    ])->toPayload();

    $this->assertNotSame('super-secret', $payload['password']);
    $this->assertStringStartsWith('enc:v1:', $payload['password']);
    $this->assertSame('super-secret', EncryptionRegistry::decrypt($payload['password']));
  }
}
