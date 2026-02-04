<?php

declare(strict_types=1);

namespace Unit\Slink\Storage\Application\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\Enum\StorageProvider;
use Slink\Storage\Application\Service\StorageUsageService;
use Slink\Storage\Domain\Exception\StorageProviderNotConfiguredException;
use Slink\Storage\Domain\Service\StorageUsageProviderInterface;
use Slink\Storage\Domain\ValueObject\StorageUsage;
use Slink\Storage\Domain\Service\StorageUsageProviderLocatorInterface;

final class StorageUsageServiceTest extends TestCase {
    private StorageUsageProviderLocatorInterface $providerLocator;

    protected function setUp(): void {
        $this->providerLocator = $this->createStub(StorageUsageProviderLocatorInterface::class);
    }

    #[Test]
    public function itReturnsCurrentUsageForLocalProvider(): void {
        $expectedUsage = new StorageUsage('local', 1024, 10240, 5);
        $provider = $this->createMock(StorageUsageProviderInterface::class);

        $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $providerLocator = $this->createMock(StorageUsageProviderLocatorInterface::class);

        $configurationProvider
            ->expects($this->once())
            ->method('get')
            ->with('storage.provider')
            ->willReturn('local');

        $providerLocator
            ->expects($this->once())
            ->method('getProvider')
            ->with(StorageProvider::Local)
            ->willReturn($provider);

        $provider
            ->expects($this->once())
            ->method('getUsage')
            ->willReturn($expectedUsage);

        $service = new StorageUsageService($providerLocator, $configurationProvider);
        $result = $service->getCurrentUsage();

        $this->assertSame($expectedUsage, $result);
    }

    #[Test]
    public function itReturnsCurrentUsageForS3Provider(): void {
        $expectedUsage = new StorageUsage('s3', 2048, null, 0);
        $provider = $this->createMock(StorageUsageProviderInterface::class);

        $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $providerLocator = $this->createMock(StorageUsageProviderLocatorInterface::class);

        $configurationProvider
            ->expects($this->once())
            ->method('get')
            ->with('storage.provider')
            ->willReturn('s3');

        $providerLocator
            ->expects($this->once())
            ->method('getProvider')
            ->with(StorageProvider::AmazonS3)
            ->willReturn($provider);

        $provider
            ->expects($this->once())
            ->method('getUsage')
            ->willReturn($expectedUsage);

        $service = new StorageUsageService($providerLocator, $configurationProvider);
        $result = $service->getCurrentUsage();

        $this->assertSame($expectedUsage, $result);
    }

    #[Test]
    public function itReturnsCurrentUsageForSmbProvider(): void {
        $expectedUsage = new StorageUsage('smb', 4096, null, 12);
        $provider = $this->createMock(StorageUsageProviderInterface::class);

        $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $providerLocator = $this->createMock(StorageUsageProviderLocatorInterface::class);

        $configurationProvider
            ->expects($this->once())
            ->method('get')
            ->with('storage.provider')
            ->willReturn('smb');

        $providerLocator
            ->expects($this->once())
            ->method('getProvider')
            ->with(StorageProvider::SmbShare)
            ->willReturn($provider);

        $provider
            ->expects($this->once())
            ->method('getUsage')
            ->willReturn($expectedUsage);

        $service = new StorageUsageService($providerLocator, $configurationProvider);
        $result = $service->getCurrentUsage();

        $this->assertSame($expectedUsage, $result);
    }

    #[Test]
    public function itThrowsExceptionWhenProviderIsNotConfigured(): void {
        $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $configurationProvider
            ->expects($this->once())
            ->method('get')
            ->with('storage.provider')
            ->willReturn(null);

        $service = new StorageUsageService($this->providerLocator, $configurationProvider);

        $this->expectException(StorageProviderNotConfiguredException::class);

        $service->getCurrentUsage();
    }

    #[Test]
    public function itThrowsExceptionWhenProviderIsEmpty(): void {
        $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $configurationProvider
            ->expects($this->once())
            ->method('get')
            ->with('storage.provider')
            ->willReturn('');

        $service = new StorageUsageService($this->providerLocator, $configurationProvider);

        $this->expectException(StorageProviderNotConfiguredException::class);

        $service->getCurrentUsage();
    }
}