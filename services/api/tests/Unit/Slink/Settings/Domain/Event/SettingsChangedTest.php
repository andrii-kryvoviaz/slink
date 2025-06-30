<?php

declare(strict_types=1);

namespace Unit\Slink\Settings\Domain\Event;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Event\SettingsChanged;
use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\ValueObject\User\UserSettings;
use Slink\Settings\Domain\ValueObject\User\PasswordSettings;

final class SettingsChangedTest extends TestCase {
    #[Test]
    public function itShouldCreateSettingsChangedEvent(): void {
        $category = SettingCategory::User;
        $settings = $this->createUserSettings();
        
        $event = new SettingsChanged($category, $settings);
        
        $this->assertSame($category, $event->category);
        $this->assertSame($settings, $event->settings);
    }

    #[Test]
    public function itShouldSerializeToPayload(): void {
        $category = SettingCategory::User;
        $settings = $this->createUserSettings();
        $event = new SettingsChanged($category, $settings);
        
        $payload = $event->toPayload();
        
        $this->assertArrayHasKey('category', $payload);
        $this->assertArrayHasKey('settings', $payload);
        $this->assertSame('user', $payload['category']);
        $this->assertIsArray($payload['settings']);
    }

    #[Test]
    public function itShouldDeserializeFromPayload(): void {
        $originalSettings = $this->createUserSettings();
        $payload = [
            'category' => 'user',
            'settings' => $originalSettings->toNormalizedPayload()
        ];
        
        $event = SettingsChanged::fromPayload($payload);
        
        $this->assertSame(SettingCategory::User, $event->category);
        $this->assertInstanceOf(UserSettings::class, $event->settings);
        $this->assertEquals($originalSettings->toPayload(), $event->settings->toPayload());
    }

    #[Test]
    public function itShouldHandleSerializationRoundTrip(): void {
        $originalCategory = SettingCategory::User;
        $originalSettings = $this->createUserSettings();
        $originalEvent = new SettingsChanged($originalCategory, $originalSettings);
        
        $payload = $originalEvent->toPayload();
        $deserializedEvent = SettingsChanged::fromPayload($payload);
        
        $this->assertEquals($originalEvent->category, $deserializedEvent->category);
        $this->assertEquals($originalEvent->settings->toPayload(), $deserializedEvent->settings->toPayload());
    }

    #[Test]
    public function itShouldHandleDifferentSettingCategories(): void {
        $category = SettingCategory::Image;
        $settings = $this->createImageSettings();
        $event = new SettingsChanged($category, $settings);
        
        $payload = $event->toPayload();
        $deserializedEvent = SettingsChanged::fromPayload($payload);
        
        $this->assertSame(SettingCategory::Image, $deserializedEvent->category);
        $this->assertEquals($settings->toPayload(), $deserializedEvent->settings->toPayload());
    }

    private function createUserSettings(): UserSettings {
        return UserSettings::fromPayload([
            'approvalRequired' => true,
            'allowRegistration' => true,
            'allowUnauthenticatedAccess' => false,
            'password' => [
                'minLength' => 8,
                'requirements' => 0
            ]
        ]);
    }

    private function createImageSettings(): \Slink\Settings\Domain\ValueObject\Image\ImageSettings {
        return \Slink\Settings\Domain\ValueObject\Image\ImageSettings::fromPayload([
            'maxSize' => '5M',
            'stripExifMetadata' => true,
            'compressionQuality' => 85
        ]);
    }
}
