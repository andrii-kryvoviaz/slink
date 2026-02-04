<?php

declare(strict_types=1);

namespace Unit\Slink\Settings\Application\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Enum\ConfigurationProvider;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Settings\Infrastructure\Provider\ConfigurationProviderLocator;

final class SettingsServiceTest extends TestCase {
    /**
     * @return array{SettingsService, ConfigurationProviderInterface&\PHPUnit\Framework\MockObject\MockObject, ConfigurationProviderInterface&\PHPUnit\Framework\MockObject\MockObject}
     */
    private function createSettingsServiceWithMocks(): array {
        $configurationLocator = $this->createStub(ConfigurationProviderLocator::class);
        $storeProvider = $this->createMock(ConfigurationProviderInterface::class);
        $defaultProvider = $this->createMock(ConfigurationProviderInterface::class);

        $configurationLocator
            ->method('get')
            ->willReturnMap([
                [ConfigurationProvider::Store, $storeProvider],
                [ConfigurationProvider::Default, $defaultProvider],
            ]);

        $settingsService = new SettingsService($configurationLocator);

        return [$settingsService, $storeProvider, $defaultProvider];
    }

    #[Test]
    public function itShouldGetValueFromStoreProviderWhenAvailable(): void {
        $key = 'user.approvalRequired';
        $expectedValue = true;

        [$settingsService, $storeProvider, $defaultProvider] = $this->createSettingsServiceWithMocks();

        $storeProvider
            ->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn($expectedValue);

        $defaultProvider
            ->expects($this->never())
            ->method('get');

        $result = $settingsService->get($key);

        $this->assertSame($expectedValue, $result);
    }

    #[Test]
    public function itShouldFallbackToDefaultProviderWhenStoreReturnsNull(): void {
        $key = 'user.approvalRequired';
        $expectedValue = false;

        [$settingsService, $storeProvider, $defaultProvider] = $this->createSettingsServiceWithMocks();

        $storeProvider
            ->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(null);

        $defaultProvider
            ->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn($expectedValue);

        $result = $settingsService->get($key);

        $this->assertSame($expectedValue, $result);
    }

    #[Test]
    public function itShouldReturnNullWhenBothProvidersReturnNull(): void {
        $key = 'non.existent.key';

        [$settingsService, $storeProvider, $defaultProvider] = $this->createSettingsServiceWithMocks();

        $storeProvider
            ->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(null);

        $defaultProvider
            ->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(null);

        $result = $settingsService->get($key);

        $this->assertNull($result);
    }

    #[Test]
    public function itShouldCheckHasInStoreProviderFirst(): void {
        $key = 'user.approvalRequired';

        [$settingsService, $storeProvider, $defaultProvider] = $this->createSettingsServiceWithMocks();

        $storeProvider
            ->expects($this->once())
            ->method('has')
            ->with($key)
            ->willReturn(true);

        $defaultProvider
            ->expects($this->never())
            ->method('has');

        $result = $settingsService->has($key);

        $this->assertTrue($result);
    }

    #[Test]
    public function itShouldCheckHasInDefaultProviderWhenStoreReturnsFalse(): void {
        $key = 'user.approvalRequired';

        [$settingsService, $storeProvider, $defaultProvider] = $this->createSettingsServiceWithMocks();

        $storeProvider
            ->expects($this->once())
            ->method('has')
            ->with($key)
            ->willReturn(false);

        $defaultProvider
            ->expects($this->once())
            ->method('has')
            ->with($key)
            ->willReturn(true);

        $result = $settingsService->has($key);

        $this->assertTrue($result);
    }

    #[Test]
    public function itShouldReturnFalseWhenBothProvidersReturnFalse(): void {
        $key = 'non.existent.key';

        [$settingsService, $storeProvider, $defaultProvider] = $this->createSettingsServiceWithMocks();

        $storeProvider
            ->expects($this->once())
            ->method('has')
            ->with($key)
            ->willReturn(false);

        $defaultProvider
            ->expects($this->once())
            ->method('has')
            ->with($key)
            ->willReturn(false);

        $result = $settingsService->has($key);

        $this->assertFalse($result);
    }

    #[Test]
    public function itShouldMergeAllSettingsWithStoreOverridingDefaults(): void {
        $defaultSettings = [
            'user' => [
                'approvalRequired' => true,
                'allowRegistration' => true,
            ],
            'image' => [
                'maxSize' => 1048576,
            ],
        ];

        $storeSettings = [
            'user' => [
                'approvalRequired' => false,
            ],
            'storage' => [
                'driver' => 'local',
            ],
        ];

        $expectedMerged = [
            'user' => [
                'approvalRequired' => false,
                'allowRegistration' => true,
            ],
            'image' => [
                'maxSize' => 1048576,
            ],
            'storage' => [
                'driver' => 'local',
            ],
        ];

        [$settingsService, $storeProvider, $defaultProvider] = $this->createSettingsServiceWithMocks();

        $defaultProvider
            ->expects($this->once())
            ->method('all')
            ->willReturn($defaultSettings);

        $storeProvider
            ->expects($this->once())
            ->method('all')
            ->willReturn($storeSettings);

        $result = $settingsService->all();

        $this->assertSame($expectedMerged, $result);
    }

    #[Test]
    public function itShouldHandleEmptyStoreSettings(): void {
        $defaultSettings = [
            'user' => [
                'approvalRequired' => true,
            ],
        ];

        [$settingsService, $storeProvider, $defaultProvider] = $this->createSettingsServiceWithMocks();

        $defaultProvider
            ->expects($this->once())
            ->method('all')
            ->willReturn($defaultSettings);

        $storeProvider
            ->expects($this->once())
            ->method('all')
            ->willReturn([]);

        $result = $settingsService->all();

        $this->assertSame($defaultSettings, $result);
    }

    #[Test]
    public function itShouldHandleEmptyDefaultSettings(): void {
        $storeSettings = [
            'user' => [
                'approvalRequired' => false,
            ],
        ];

        [$settingsService, $storeProvider, $defaultProvider] = $this->createSettingsServiceWithMocks();

        $defaultProvider
            ->expects($this->once())
            ->method('all')
            ->willReturn([]);

        $storeProvider
            ->expects($this->once())
            ->method('all')
            ->willReturn($storeSettings);

        $result = $settingsService->all();

        $this->assertSame($storeSettings, $result);
    }

    #[Test]
    public function itShouldHandleContainerExceptionsDuringConstruction(): void {
        $locator = $this->createStub(ConfigurationProviderLocator::class);
        $locator->method('get')
            ->willThrowException(new class extends \Exception implements ContainerExceptionInterface {});

        $this->expectException(ContainerExceptionInterface::class);

        new SettingsService($locator);
    }

    #[Test]
    public function itShouldHandleNotFoundExceptionsDuringConstruction(): void {
        $locator = $this->createStub(ConfigurationProviderLocator::class);
        $locator->method('get')
            ->willThrowException(new class extends \Exception implements NotFoundExceptionInterface {});

        $this->expectException(NotFoundExceptionInterface::class);

        new SettingsService($locator);
    }
}
