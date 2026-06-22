<?php

declare(strict_types=1);

namespace Unit\Slink\Settings\Application\Command\SaveSettings;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Application\Command\SaveSettings\SaveSettingsCommand;
use Slink\Settings\Application\Command\SaveSettings\SaveSettingsHandler;
use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\Repository\SettingStoreRepositoryInterface;
use Slink\Settings\Domain\Settings;
use Slink\Settings\Domain\ValueObject\Storage\StorageSettings;
use Slink\Settings\Domain\ValueObject\User\UserSettings;
use Slink\Shared\Domain\Enum\StorageProvider;
use Slink\Shared\Infrastructure\Encryption\EncryptionRegistry;
use Slink\Shared\Infrastructure\Encryption\EncryptionService;

final class SaveSettingsHandlerTest extends TestCase {

    #[Test]
    public function itShouldSaveUserSettings(): void {
        $settingsData = [
            'approvalRequired' => false,
            'allowRegistration' => true,
            'password' => [
                'minLength' => 10,
                'requirements' => 1
            ]
        ];

        $command = new SaveSettingsCommand('user', $settingsData);

        $settings = $this->createMock(Settings::class);
        $store = $this->createMock(SettingStoreRepositoryInterface::class);

        $store
            ->expects($this->once())
            ->method('get')
            ->willReturn($settings);

        $settings
            ->expects($this->once())
            ->method('setSettings')
            ->with($this->callback(function ($s) {
                return $s instanceof UserSettings
                    && $s->getSettingsCategory() === SettingCategory::User;
            }));

        $store
            ->expects($this->once())
            ->method('store')
            ->with($settings);

        $handler = new SaveSettingsHandler($store);
        $handler->__invoke($command);
    }

    #[Test]
    public function itShouldSaveImageSettings(): void {
        $settingsData = [
            'maxSize' => '10M',
            'stripExifMetadata' => false,
            'compressionQuality' => 90,
            'allowGuestUploads' => true
        ];

        $command = new SaveSettingsCommand('image', $settingsData);

        $settings = $this->createMock(Settings::class);
        $store = $this->createMock(SettingStoreRepositoryInterface::class);

        $store
            ->expects($this->once())
            ->method('get')
            ->willReturn($settings);

        $settings
            ->expects($this->once())
            ->method('setSettings')
            ->with($this->callback(function ($s) {
                return $s instanceof \Slink\Settings\Domain\ValueObject\Image\ImageSettings
                    && $s->getSettingsCategory() === SettingCategory::Image;
            }));

        $store
            ->expects($this->once())
            ->method('store')
            ->with($settings);

        $handler = new SaveSettingsHandler($store);
        $handler->__invoke($command);
    }

    #[Test]
    public function itPersistsStorageSettingsThroughCommandPath(): void {
        EncryptionRegistry::setService(new EncryptionService('test-secret-key'));

        $settingsData = ['provider' => 's3', 'adapter' => [
            'local' => null,
            'smb' => null,
            's3' => ['region' => 'us-east-1', 'bucket' => 'my-bucket', 'key' => 'access-key', 'secret' => 'secret-key', 'useCustomProvider' => false],
        ]];

        $command = new SaveSettingsCommand('storage', $settingsData);

        $captured = null;
        $settings = $this->createStub(Settings::class);
        $settings->method('setSettings')->willReturnCallback(function ($value) use (&$captured) {
            $captured = $value;
        });

        $store = $this->createStub(SettingStoreRepositoryInterface::class);
        $store->method('get')->willReturn($settings);

        (new SaveSettingsHandler($store))->__invoke($command);

        $this->assertInstanceOf(StorageSettings::class, $captured);
        $this->assertSame(SettingCategory::Storage, $captured->getSettingsCategory());
        $this->assertSame(StorageProvider::AmazonS3->value, $captured->toPayload()['provider']);
    }

    #[Test]
    public function itShouldHandleComplexNestedSettings(): void {
        $settingsData = [
            'approvalRequired' => true,
            'allowRegistration' => false,
            'password' => [
                'minLength' => 12,
                'requirements' => 7
            ]
        ];

        $command = new SaveSettingsCommand('user', $settingsData);

        $settings = $this->createMock(Settings::class);
        $store = $this->createMock(SettingStoreRepositoryInterface::class);

        $store
            ->expects($this->once())
            ->method('get')
            ->willReturn($settings);

        $settings
            ->expects($this->once())
            ->method('setSettings')
            ->with($this->callback(function ($s) use ($settingsData) {
                if (!$s instanceof UserSettings) {
                    return false;
                }

                $payload = $s->toPayload();
                return $payload['approvalRequired'] === $settingsData['approvalRequired']
                    && $payload['allowRegistration'] === $settingsData['allowRegistration'];
            }));

        $store
            ->expects($this->once())
            ->method('store')
            ->with($settings);

        $handler = new SaveSettingsHandler($store);
        $handler->__invoke($command);
    }

    #[Test]
    public function itShouldPropagateStoreExceptions(): void {
        $command = new SaveSettingsCommand('user', ['approvalRequired' => true]);

        $store = $this->createMock(SettingStoreRepositoryInterface::class);
        $store
            ->expects($this->once())
            ->method('get')
            ->willThrowException(new \RuntimeException('Store error'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Store error');

        $handler = new SaveSettingsHandler($store);
        $handler->__invoke($command);
    }

    #[Test]
    public function itShouldPropagateSettingsExceptions(): void {
        $command = new SaveSettingsCommand('user', [
            'approvalRequired' => true,
            'password' => [
                'minLength' => 8,
                'requirements' => 0
            ]
        ]);

        $settings = $this->createMock(Settings::class);
        $store = $this->createMock(SettingStoreRepositoryInterface::class);

        $store
            ->expects($this->once())
            ->method('get')
            ->willReturn($settings);

        $settings
            ->expects($this->once())
            ->method('setSettings')
            ->willThrowException(new \RuntimeException('Settings error'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Settings error');

        $handler = new SaveSettingsHandler($store);
        $handler->__invoke($command);
    }

    #[Test]
    public function itShouldCallStoreInCorrectSequence(): void {
        $command = new SaveSettingsCommand('user', [
            'approvalRequired' => true,
            'password' => [
                'minLength' => 8,
                'requirements' => 0
            ]
        ]);

        $callOrder = [];

        $settings = $this->createMock(Settings::class);
        $store = $this->createMock(SettingStoreRepositoryInterface::class);

        $store
            ->expects($this->once())
            ->method('get')
            ->willReturnCallback(function () use (&$callOrder, $settings) {
                $callOrder[] = 'get';
                return $settings;
            });

        $settings
            ->expects($this->once())
            ->method('setSettings')
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'setSettings';
            });

        $store
            ->expects($this->once())
            ->method('store')
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'store';
            });

        $handler = new SaveSettingsHandler($store);
        $handler->__invoke($command);

        $this->assertSame(['get', 'setSettings', 'store'], $callOrder);
    }
}
