<?php

declare(strict_types=1);

namespace Unit\Slink\Settings\Application\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Enum\ConfigurationProvider;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Settings\Infrastructure\Provider\ConfigurationProviderLocator;

final class SettingsServiceTest extends TestCase {
    private MockObject $configurationLocator;
    private MockObject $storeProvider;
    private MockObject $defaultProvider;
    private SettingsService $settingsService;

    protected function setUp(): void {
        $this->configurationLocator = $this->createMock(ConfigurationProviderLocator::class);
        $this->storeProvider = $this->createMock(ConfigurationProviderInterface::class);
        $this->defaultProvider = $this->createMock(ConfigurationProviderInterface::class);

        $this->configurationLocator
            ->method('get')
            ->willReturnMap([
                [ConfigurationProvider::Store, $this->storeProvider],
                [ConfigurationProvider::Default, $this->defaultProvider],
            ]);

        $this->settingsService = new SettingsService($this->configurationLocator);
    }

    #[Test]
    public function itShouldGetValueFromStoreProviderWhenAvailable(): void {
        $key = 'user.approvalRequired';
        $expectedValue = true;

        $this->storeProvider
            ->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn($expectedValue);

        $this->defaultProvider
            ->expects($this->never())
            ->method('get');

        $result = $this->settingsService->get($key);

        $this->assertSame($expectedValue, $result);
    }

    #[Test]
    public function itShouldFallbackToDefaultProviderWhenStoreReturnsNull(): void {
        $key = 'user.approvalRequired';
        $expectedValue = false;

        $this->storeProvider
            ->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(null);

        $this->defaultProvider
            ->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn($expectedValue);

        $result = $this->settingsService->get($key);

        $this->assertSame($expectedValue, $result);
    }

    #[Test]
    public function itShouldReturnNullWhenBothProvidersReturnNull(): void {
        $key = 'non.existent.key';

        $this->storeProvider
            ->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(null);

        $this->defaultProvider
            ->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(null);

        $result = $this->settingsService->get($key);

        $this->assertNull($result);
    }

    #[Test]
    public function itShouldCheckHasInStoreProviderFirst(): void {
        $key = 'user.approvalRequired';

        $this->storeProvider
            ->expects($this->once())
            ->method('has')
            ->with($key)
            ->willReturn(true);

        $this->defaultProvider
            ->expects($this->never())
            ->method('has');

        $result = $this->settingsService->has($key);

        $this->assertTrue($result);
    }

    #[Test]
    public function itShouldCheckHasInDefaultProviderWhenStoreReturnsFalse(): void {
        $key = 'user.approvalRequired';

        $this->storeProvider
            ->expects($this->once())
            ->method('has')
            ->with($key)
            ->willReturn(false);

        $this->defaultProvider
            ->expects($this->once())
            ->method('has')
            ->with($key)
            ->willReturn(true);

        $result = $this->settingsService->has($key);

        $this->assertTrue($result);
    }

    #[Test]
    public function itShouldReturnFalseWhenBothProvidersReturnFalse(): void {
        $key = 'non.existent.key';

        $this->storeProvider
            ->expects($this->once())
            ->method('has')
            ->with($key)
            ->willReturn(false);

        $this->defaultProvider
            ->expects($this->once())
            ->method('has')
            ->with($key)
            ->willReturn(false);

        $result = $this->settingsService->has($key);

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

        $this->defaultProvider
            ->expects($this->once())
            ->method('all')
            ->willReturn($defaultSettings);

        $this->storeProvider
            ->expects($this->once())
            ->method('all')
            ->willReturn($storeSettings);

        $result = $this->settingsService->all();

        $this->assertSame($expectedMerged, $result);
    }

    #[Test]
    public function itShouldHandleEmptyStoreSettings(): void {
        $defaultSettings = [
            'user' => [
                'approvalRequired' => true,
            ],
        ];

        $this->defaultProvider
            ->expects($this->once())
            ->method('all')
            ->willReturn($defaultSettings);

        $this->storeProvider
            ->expects($this->once())
            ->method('all')
            ->willReturn([]);

        $result = $this->settingsService->all();

        $this->assertSame($defaultSettings, $result);
    }

    #[Test]
    public function itShouldHandleEmptyDefaultSettings(): void {
        $storeSettings = [
            'user' => [
                'approvalRequired' => false,
            ],
        ];

        $this->defaultProvider
            ->expects($this->once())
            ->method('all')
            ->willReturn([]);

        $this->storeProvider
            ->expects($this->once())
            ->method('all')
            ->willReturn($storeSettings);

        $result = $this->settingsService->all();

        $this->assertSame($storeSettings, $result);
    }

    #[Test]
    public function itShouldHandleContainerExceptionsDuringConstruction(): void {
        $locator = $this->createMock(ConfigurationProviderLocator::class);
        $locator->method('get')
            ->willThrowException(new class extends \Exception implements ContainerExceptionInterface {});

        $this->expectException(ContainerExceptionInterface::class);

        new SettingsService($locator);
    }

    #[Test]
    public function itShouldHandleNotFoundExceptionsDuringConstruction(): void {
        $locator = $this->createMock(ConfigurationProviderLocator::class);
        $locator->method('get')
            ->willThrowException(new class extends \Exception implements NotFoundExceptionInterface {});

        $this->expectException(NotFoundExceptionInterface::class);

        new SettingsService($locator);
    }
}
