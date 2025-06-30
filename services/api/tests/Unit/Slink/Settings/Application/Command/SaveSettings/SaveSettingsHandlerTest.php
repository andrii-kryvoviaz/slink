<?php

declare(strict_types=1);

namespace Unit\Slink\Settings\Application\Command\SaveSettings;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Slink\Settings\Application\Command\SaveSettings\SaveSettingsCommand;
use Slink\Settings\Application\Command\SaveSettings\SaveSettingsHandler;
use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\Repository\SettingStoreRepositoryInterface;
use Slink\Settings\Domain\Settings;
use Slink\Settings\Domain\ValueObject\User\UserSettings;

final class SaveSettingsHandlerTest extends TestCase {
    private MockObject $store;
    private MockObject $settings;
    private SaveSettingsHandler $handler;

    protected function setUp(): void {
        $this->store = $this->createMock(SettingStoreRepositoryInterface::class);
        $this->settings = $this->createMock(Settings::class);
        $this->handler = new SaveSettingsHandler($this->store);
    }

    #[Test]
    public function itShouldSaveUserSettings(): void {
        $settingsData = [
            'approvalRequired' => false,
            'allowRegistration' => true,
            'allowUnauthenticatedAccess' => true,
            'password' => [
                'minLength' => 10,
                'requirements' => 1
            ]
        ];
        
        $command = new SaveSettingsCommand('user', $settingsData);

        $this->store
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->settings);

        $this->settings
            ->expects($this->once())
            ->method('setSettings')
            ->with($this->callback(function ($settings) {
                return $settings instanceof UserSettings 
                    && $settings->getSettingsCategory() === SettingCategory::User;
            }));

        $this->store
            ->expects($this->once())
            ->method('store')
            ->with($this->settings);

        $this->handler->__invoke($command);
    }

    #[Test]
    public function itShouldSaveImageSettings(): void {
        $settingsData = [
            'maxSize' => '10M',
            'stripExifMetadata' => false,
            'compressionQuality' => 90
        ];
        
        $command = new SaveSettingsCommand('image', $settingsData);

        $this->store
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->settings);

        $this->settings
            ->expects($this->once())
            ->method('setSettings')
            ->with($this->callback(function ($settings) {
                return $settings instanceof \Slink\Settings\Domain\ValueObject\Image\ImageSettings
                    && $settings->getSettingsCategory() === SettingCategory::Image;
            }));

        $this->store
            ->expects($this->once())
            ->method('store')
            ->with($this->settings);

        $this->handler->__invoke($command);
    }

    #[Test]
    public function itShouldSaveStorageSettings(): void {
        $settingsData = [
            'provider' => 's3',
            'adapter' => [
                'local' => null,
                'smb_share' => null,
                'amazon_s3' => [
                    'region' => 'us-east-1',
                    'bucket' => 'my-bucket',
                    'key' => 'access-key',
                    'secret' => 'secret-key'
                ]
            ]
        ];
        
        $command = new SaveSettingsCommand('storage', $settingsData);

        $this->store
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->settings);

        $this->settings
            ->expects($this->once())
            ->method('setSettings')
            ->with($this->callback(function ($settings) {
                return $settings instanceof \Slink\Settings\Domain\ValueObject\Storage\StorageSettings
                    && $settings->getSettingsCategory() === SettingCategory::Storage;
            }));

        $this->store
            ->expects($this->once())
            ->method('store')
            ->with($this->settings);

        $this->handler->__invoke($command);
    }

    #[Test]
    public function itShouldHandleComplexNestedSettings(): void {
        $settingsData = [
            'approvalRequired' => true,
            'allowRegistration' => false,
            'allowUnauthenticatedAccess' => false,
            'password' => [
                'minLength' => 12,
                'requirements' => 7
            ]
        ];
        
        $command = new SaveSettingsCommand('user', $settingsData);

        $this->store
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->settings);

        $this->settings
            ->expects($this->once())
            ->method('setSettings')
            ->with($this->callback(function ($settings) use ($settingsData) {
                if (!$settings instanceof UserSettings) {
                    return false;
                }
                
                $payload = $settings->toPayload();
                return $payload['approvalRequired'] === $settingsData['approvalRequired']
                    && $payload['allowRegistration'] === $settingsData['allowRegistration']
                    && $payload['allowUnauthenticatedAccess'] === $settingsData['allowUnauthenticatedAccess'];
            }));

        $this->store
            ->expects($this->once())
            ->method('store')
            ->with($this->settings);

        $this->handler->__invoke($command);
    }

    #[Test]
    public function itShouldPropagateStoreExceptions(): void {
        $command = new SaveSettingsCommand('user', ['approvalRequired' => true]);
        
        $this->store
            ->expects($this->once())
            ->method('get')
            ->willThrowException(new \RuntimeException('Store error'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Store error');

        $this->handler->__invoke($command);
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
        
        $this->store
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->settings);

        $this->settings
            ->expects($this->once())
            ->method('setSettings')
            ->willThrowException(new \RuntimeException('Settings error'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Settings error');

        $this->handler->__invoke($command);
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
        
        $this->store
            ->expects($this->once())
            ->method('get')
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'get';
                return $this->settings;
            });

        $this->settings
            ->expects($this->once())
            ->method('setSettings')
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'setSettings';
            });

        $this->store
            ->expects($this->once())
            ->method('store')
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'store';
            });

        $this->handler->__invoke($command);

        $this->assertSame(['get', 'setSettings', 'store'], $callOrder);
    }
}
