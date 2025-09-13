<?php

declare(strict_types=1);

namespace Unit\Slink\Storage\Application\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\Enum\StorageProvider;
use Slink\Storage\Application\Service\StorageUsageService;
use Slink\Storage\Domain\Exception\StorageProviderNotConfiguredException;
use Slink\Storage\Domain\Service\StorageUsageProviderInterface;
use Slink\Storage\Domain\ValueObject\StorageUsage;
use Slink\Storage\Domain\Service\StorageUsageProviderLocatorInterface;

final class StorageUsageServiceTest extends TestCase {
    /** @var MockObject&StorageUsageProviderLocatorInterface */
    private StorageUsageProviderLocatorInterface $providerLocator;
    /** @var MockObject&ConfigurationProviderInterface */
    private ConfigurationProviderInterface $configurationProvider;
    private StorageUsageService $service;

    protected function setUp(): void {
        $this->providerLocator = $this->createMock(StorageUsageProviderLocatorInterface::class);
        $this->configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $this->service = new StorageUsageService($this->providerLocator, $this->configurationProvider);
    }

    #[Test]
    public function itReturnsCurrentUsageForLocalProvider(): void {
        $expectedUsage = new StorageUsage('local', 1024, 10240, 5);
        $provider = $this->createMock(StorageUsageProviderInterface::class);

        $this->configurationProvider
            ->expects($this->once())
            ->method('get')
            ->with('storage.provider')
            ->willReturn('local');

        $this->providerLocator
            ->expects($this->once())
            ->method('getProvider')
            ->with(StorageProvider::Local)
            ->willReturn($provider);

        $provider
            ->expects($this->once())
            ->method('getUsage')
            ->willReturn($expectedUsage);

        $result = $this->service->getCurrentUsage();

        $this->assertSame($expectedUsage, $result);
    }

    #[Test]
    public function itReturnsCurrentUsageForS3Provider(): void {
        $expectedUsage = new StorageUsage('s3', 2048, null, 0);
        $provider = $this->createMock(StorageUsageProviderInterface::class);

        $this->configurationProvider
            ->expects($this->once())
            ->method('get')
            ->with('storage.provider')
            ->willReturn('s3');

        $this->providerLocator
            ->expects($this->once())
            ->method('getProvider')
            ->with(StorageProvider::AmazonS3)
            ->willReturn($provider);

        $provider
            ->expects($this->once())
            ->method('getUsage')
            ->willReturn($expectedUsage);

        $result = $this->service->getCurrentUsage();

        $this->assertSame($expectedUsage, $result);
    }

    #[Test]
    public function itReturnsCurrentUsageForSmbProvider(): void {
        $expectedUsage = new StorageUsage('smb', 4096, null, 12);
        $provider = $this->createMock(StorageUsageProviderInterface::class);

        $this->configurationProvider
            ->expects($this->once())
            ->method('get')
            ->with('storage.provider')
            ->willReturn('smb');

        $this->providerLocator
            ->expects($this->once())
            ->method('getProvider')
            ->with(StorageProvider::SmbShare)
            ->willReturn($provider);

        $provider
            ->expects($this->once())
            ->method('getUsage')
            ->willReturn($expectedUsage);

        $result = $this->service->getCurrentUsage();

        $this->assertSame($expectedUsage, $result);
    }

    #[Test]
    public function itThrowsExceptionWhenProviderIsNotConfigured(): void {
        $this->configurationProvider
            ->expects($this->once())
            ->method('get')
            ->with('storage.provider')
            ->willReturn(null);

        $this->expectException(StorageProviderNotConfiguredException::class);

        $this->service->getCurrentUsage();
    }

    #[Test]
    public function itThrowsExceptionWhenProviderIsEmpty(): void {
        $this->configurationProvider
            ->expects($this->once())
            ->method('get')
            ->with('storage.provider')
            ->willReturn('');

        $this->expectException(StorageProviderNotConfiguredException::class);

        $this->service->getCurrentUsage();
    }
}