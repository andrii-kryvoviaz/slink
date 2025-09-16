<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Settings\Infrastructure\Persistence\EventStore;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Settings;
use Slink\Settings\Domain\ValueObject\Access\AccessSettings;
use Slink\Settings\Domain\ValueObject\User\UserSettings;
use Slink\Settings\Domain\ValueObject\Image\ImageSettings;
use Slink\Settings\Domain\ValueObject\Storage\StorageSettings;
use Slink\Shared\Domain\ValueObject\ID;
use Tests\Traits\PrivatePropertyTrait;

final class SettingsSnapshotTest extends TestCase {
  use PrivatePropertyTrait;

  #[Test]
  public function itCreatesSnapshotFromSettings(): void {
    $settings = $this->createSettingsWithData();

    $reflection = new \ReflectionClass($settings);
    $method = $reflection->getMethod('createSnapshotState');
    $method->setAccessible(true);
    $snapshot = $method->invoke($settings);

    $this->assertArrayHasKey('user', $snapshot);
    $this->assertArrayHasKey('image', $snapshot);
    $this->assertArrayHasKey('storage', $snapshot);
    $this->assertArrayHasKey('access', $snapshot);
    
    $this->assertIsArray($snapshot['user']);
    $this->assertIsArray($snapshot['image']);
    $this->assertIsArray($snapshot['storage']);
    $this->assertIsArray($snapshot['access']);
  }

  #[Test]
  public function itRestoresSettingsFromSnapshot(): void {
    $snapshot = [
      'user' => [
        'approvalRequired' => true,
        'allowRegistration' => false,
        'allowUnauthenticatedAccess' => false,
        'password' => [
          'minLength' => 10,
          'requirements' => 1
        ]
      ],
      'image' => [
        'maxSize' => '10M',
        'stripExifMetadata' => false,
        'compressionQuality' => 90,
        'allowOnlyPublicImages' => true,
        'enableDeduplication' => false,
      ],
      'storage' => [
        'provider' => 's3',
        'adapter' => [
          'local' => null,
          'smb_share' => null,
          'amazon_s3' => [
            'bucket' => 'test-bucket',
            'region' => 'us-east-1'
          ]
        ]
      ],
      'access' => [
        'allowGuestUploads' => true,
        'allowUnauthenticatedAccess' => true,
        'requireSsl' => true,
      ]
    ];

    $settingsId = ID::fromString(Settings::getIdReference());
    $reflection = new \ReflectionClass(Settings::class);
    $method = $reflection->getMethod('reconstituteFromSnapshotState');
    $method->setAccessible(true);
    $settings = $method->invoke(null, $settingsId, $snapshot);

    $this->assertEquals('settings.global', $settings->aggregateRootId()->toString());
    
    $this->assertTrue($settings->get('user.approvalRequired'));
    $this->assertFalse($settings->get('user.allowRegistration'));
    $this->assertTrue($settings->get('access.allowUnauthenticatedAccess'));
    
    $this->assertEquals('10M', $settings->get('image.maxSize'));
    $this->assertFalse($settings->get('image.stripExifMetadata'));
    $this->assertEquals(90, $settings->get('image.compressionQuality'));
    $this->assertTrue($settings->get('image.allowOnlyPublicImages'));
    $this->assertFalse($settings->get('image.enableDeduplication'));
    
    $this->assertEquals('s3', $settings->get('storage.provider'));
    
    $this->assertTrue($settings->get('access.allowGuestUploads'));
    $this->assertTrue($settings->get('access.allowUnauthenticatedAccess'));
    $this->assertTrue($settings->get('access.requireSsl'));
  }

  #[Test]
  public function itMaintainsDataIntegrityThroughSnapshotCycle(): void {
    $originalSettings = $this->createSettingsWithData();
    
    $reflection = new \ReflectionClass($originalSettings);
    $createMethod = $reflection->getMethod('createSnapshotState');
    $createMethod->setAccessible(true);
    $snapshot = $createMethod->invoke($originalSettings);
    
    $settingsId = ID::fromString(Settings::getIdReference());
    $restoreMethod = $reflection->getMethod('reconstituteFromSnapshotState');
    $restoreMethod->setAccessible(true);
    $restoredSettings = $restoreMethod->invoke(null, $settingsId, $snapshot);

    $this->assertEquals($originalSettings->aggregateRootId(), $restoredSettings->aggregateRootId());
    
    $this->assertEquals($originalSettings->get('user.approvalRequired'), $restoredSettings->get('user.approvalRequired'));
    $this->assertEquals($originalSettings->get('user.allowRegistration'), $restoredSettings->get('user.allowRegistration'));
    $this->assertEquals($originalSettings->get('access.allowUnauthenticatedAccess'), $restoredSettings->get('access.allowUnauthenticatedAccess'));
    
    $this->assertEquals($originalSettings->get('image.maxSize'), $restoredSettings->get('image.maxSize'));
    $this->assertEquals($originalSettings->get('image.stripExifMetadata'), $restoredSettings->get('image.stripExifMetadata'));
    $this->assertEquals($originalSettings->get('image.compressionQuality'), $restoredSettings->get('image.compressionQuality'));
    $this->assertEquals($originalSettings->get('image.allowOnlyPublicImages'), $restoredSettings->get('image.allowOnlyPublicImages'));
    $this->assertEquals($originalSettings->get('image.enableDeduplication'), $restoredSettings->get('image.enableDeduplication'));
    
    $this->assertEquals($originalSettings->get('storage.provider'), $restoredSettings->get('storage.provider'));
    
    $this->assertEquals($originalSettings->get('access.allowGuestUploads'), $restoredSettings->get('access.allowGuestUploads'));
    $this->assertEquals($originalSettings->get('access.allowUnauthenticatedAccess'), $restoredSettings->get('access.allowUnauthenticatedAccess'));
    $this->assertEquals($originalSettings->get('access.requireSsl'), $restoredSettings->get('access.requireSsl'));
  }

  #[Test]
  public function itHandlesPartialSettingsInSnapshot(): void {
    $snapshot = [
      'user' => [
        'approvalRequired' => false,
        'allowRegistration' => true,
        'password' => [
          'minLength' => 6,
          'requirements' => 0
        ]
      ],
      'image' => [
        'maxSize' => '5M',
        'stripExifMetadata' => true,
        'compressionQuality' => 85,
        'allowOnlyPublicImages' => false,
        'enableDeduplication' => true,
      ],
      'storage' => [
        'provider' => 'local',
        'adapter' => [
          'local' => [
            'dir' => '/storage'
          ],
          'smb_share' => null,
          'amazon_s3' => null
        ]
      ],
      'access' => [
        'allowGuestUploads' => false,
        'allowUnauthenticatedAccess' => true,
        'requireSsl' => false,
      ]
    ];

    $settingsId = ID::fromString(Settings::getIdReference());
    $reflection = new \ReflectionClass(Settings::class);
    $method = $reflection->getMethod('reconstituteFromSnapshotState');
    $method->setAccessible(true);
    $settings = $method->invoke(null, $settingsId, $snapshot);

    $this->assertEquals('settings.global', $settings->aggregateRootId()->toString());
    
    $this->assertFalse($settings->get('user.approvalRequired'));
    $this->assertTrue($settings->get('user.allowRegistration'));
    $this->assertTrue($settings->get('access.allowUnauthenticatedAccess'));
    
    $this->assertEquals('5M', $settings->get('image.maxSize'));
    $this->assertEquals('local', $settings->get('storage.provider'));
    $this->assertFalse($settings->get('access.allowGuestUploads'));
  }

  #[Test]
  public function itHandlesCompleteSettingsSnapshot(): void {
    $userSettings = $this->createUserSettings(false, false);
    $imageSettings = $this->createImageSettings();
    $accessSettings = $this->createAccessSettings(true);
    $storageSettings = StorageSettings::fromPayload([
      'provider' => 's3',
      'adapter' => [
        'local' => null,
        'smb_share' => [
          'host' => 'smb.example.com',
          'share' => 'uploads',
          'username' => 'user',
          'password' => 'pass'
        ],
        'amazon_s3' => [
          'bucket' => 'my-bucket',
          'region' => 'eu-west-1',
          'accessKey' => 'access123',
          'secretKey' => 'secret456'
        ]
      ]
    ]);
    
    $settings = $this->createSettings();
    $settings->initialize([$userSettings, $imageSettings, $storageSettings, $accessSettings]);
    
    $reflection = new \ReflectionClass($settings);
    $createMethod = $reflection->getMethod('createSnapshotState');
    $createMethod->setAccessible(true);
    $snapshot = $createMethod->invoke($settings);
    
    $settingsId = ID::fromString(Settings::getIdReference());
    $restoreMethod = $reflection->getMethod('reconstituteFromSnapshotState');
    $restoreMethod->setAccessible(true);
    $restoredSettings = $restoreMethod->invoke(null, $settingsId, $snapshot);
    
    $this->assertEquals('s3', $restoredSettings->get('storage.provider'));
    $this->assertFalse($restoredSettings->get('user.approvalRequired'));
    $this->assertTrue($restoredSettings->get('access.allowUnauthenticatedAccess'));
  }

  private function createSettings(): Settings {
    $reflection = new \ReflectionClass(Settings::class);
    $constructor = $reflection->getConstructor();
    if ($constructor === null) {
      throw new \RuntimeException('Constructor not found');
    }
    $constructor->setAccessible(true);
    $instance = $reflection->newInstanceWithoutConstructor();
    $constructor->invoke($instance);
    return $instance;
  }

  private function createSettingsWithData(): Settings {
    $settings = $this->createSettings();
    $userSettings = $this->createUserSettings();
    $imageSettings = $this->createImageSettings();
    $storageSettings = $this->createStorageSettings();
    $accessSettings = $this->createAccessSettings();
    
    $settings->initialize([$userSettings, $imageSettings, $storageSettings, $accessSettings]);
    
    return $settings;
  }

  private function createUserSettings(
    bool $approvalRequired = true,
    bool $allowRegistration = true
  ): UserSettings {
    return UserSettings::fromPayload([
      'approvalRequired' => $approvalRequired,
      'allowRegistration' => $allowRegistration,
      'password' => [
        'minLength' => 8,
        'requirements' => 0
      ]
    ]);
  }

  private function createImageSettings(): ImageSettings {
    return ImageSettings::fromPayload([
      'maxSize' => '5M',
      'stripExifMetadata' => true,
      'compressionQuality' => 85,
      'allowOnlyPublicImages' => false,
      'enableDeduplication' => true,
    ]);
  }

  private function createAccessSettings(
    bool $allowUnauthenticatedAccess = false
  ): AccessSettings {
    return AccessSettings::fromPayload([
      'allowGuestUploads' => false,
      'allowUnauthenticatedAccess' => $allowUnauthenticatedAccess,
      'requireSsl' => false,
    ]);
  }

  private function createStorageSettings(): StorageSettings {
    return StorageSettings::fromPayload([
      'provider' => 'local',
      'adapter' => [
        'local' => [
          'dir' => '/storage'
        ],
        'smb_share' => null,
        'amazon_s3' => null
      ]
    ]);
  }
}