<?php

declare(strict_types=1);

namespace Unit\Slink\Settings\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Event\SettingsChanged;
use Slink\Settings\Domain\Exception\InvalidSettingsException;
use Slink\Settings\Domain\Settings;
use Slink\Settings\Domain\ValueObject\Access\AccessSettings;
use Slink\Settings\Domain\ValueObject\User\UserSettings;
use Slink\Settings\Domain\ValueObject\User\PasswordSettings;
use Slink\Settings\Domain\ValueObject\Image\ImageSettings;
use Slink\Settings\Domain\ValueObject\Storage\StorageSettings;
use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Shared\Domain\ValueObject\ID;
use Tests\Traits\PrivatePropertyTrait;

final class SettingsTest extends TestCase {
    use PrivatePropertyTrait;

    #[Test]
    public function itShouldReturnCorrectIdReference(): void {
        $expected = 'settings.global';
        
        $actual = Settings::getIdReference();
        
        $this->assertSame($expected, $actual);
    }

    #[Test]
    public function itShouldInitializeWithValidSettingsValueObjects(): void {
        $settings = $this->createSettings();
        $userSettings = $this->createUserSettings();
        $imageSettings = $this->createImageSettings();
        $storageSettings = $this->createStorageSettings();
        $accessSettings = $this->createAccessSettings();
        
        $settings->initialize([$userSettings, $imageSettings, $storageSettings, $accessSettings]);
        
        $this->assertSame($userSettings, $this->getPrivateProperty($settings, 'user'));
        $this->assertSame($imageSettings, $this->getPrivateProperty($settings, 'image'));
        $this->assertSame($storageSettings, $this->getPrivateProperty($settings, 'storage'));
        $this->assertSame($accessSettings, $this->getPrivateProperty($settings, 'access'));
    }

    #[Test]
    public function itShouldThrowExceptionWhenInitializingWithInvalidValueObject(): void {
        $settings = $this->createSettings();
        $invalidObject = new \stdClass();
        
        $this->expectException(InvalidSettingsException::class);
        $this->expectExceptionMessage('Invalid Settings Value Object provided');
        
        /** @phpstan-ignore-next-line */
        $settings->initialize([$invalidObject]);
    }

    #[Test]
    public function itShouldGetNestedSettingValue(): void {
        $settings = $this->createSettingsWithData();
        
        $result = $settings->get('user.approvalRequired');
        
        $this->assertTrue($result);
    }

    #[Test]
    public function itShouldGetDeeplyNestedSettingValue(): void {
        $settings = $this->createSettingsWithData();
        
        $result = $settings->get('user.password.minLength');
        
        $this->assertSame(8, $result);
    }

    #[Test]
    public function itShouldThrowExceptionForInvalidRootKey(): void {
        $settings = $this->createSettingsWithData();
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid Settings key');
        
        $settings->get('nonexistent.key');
    }

    #[Test]
    public function itShouldThrowExceptionForInvalidNestedKey(): void {
        $settings = $this->createSettingsWithData();
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid Settings key');
        
        $settings->get('user.nonexistent');
    }

    #[Test]
    public function itShouldSetSettingsAndRecordEvent(): void {
        $settings = $this->createSettingsWithData();
        $newUserSettings = $this->createUserSettings(false, false, true);
        
        $settings->setSettings($newUserSettings);
        
        $events = $settings->releaseEvents();
        $this->assertCount(1, $events);
        
        $event = $events[0];
        $this->assertInstanceOf(SettingsChanged::class, $event);
        $this->assertSame(SettingCategory::User, $event->category);
        $this->assertSame($newUserSettings, $event->settings);
    }

    #[Test]
    public function itShouldThrowExceptionForUninitializedProperty(): void {
        $settings = $this->createSettings();
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid Settings key');
        
        $settings->get('storage.driver');
    }

    #[Test]
    public function itShouldApplySettingsChangedEvent(): void {
        $settings = $this->createSettingsWithData();
        $newUserSettings = $this->createUserSettings(false, false, true);
        $event = new SettingsChanged(SettingCategory::User, $newUserSettings);
        
        $settings->applySettingsChanged($event);
        
        $appliedSettings = $this->getPrivateProperty($settings, 'user');
        $this->assertSame($newUserSettings, $appliedSettings);
    }

    private function createSettings(): Settings {
        $reflection = new \ReflectionClass(Settings::class);
        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            throw new \RuntimeException('Constructor not found');
        }

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
        bool $allowRegistration = true,
        bool $allowUnauthenticatedAccess = false
    ): UserSettings {
        return UserSettings::fromPayload([
            'approvalRequired' => $approvalRequired,
            'allowRegistration' => $allowRegistration,
            'allowUnauthenticatedAccess' => $allowUnauthenticatedAccess,
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

    private function createAccessSettings(): AccessSettings {
        return AccessSettings::fromPayload([
            'allowGuestUploads' => false,
            'allowUnauthenticatedAccess' => false,
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
