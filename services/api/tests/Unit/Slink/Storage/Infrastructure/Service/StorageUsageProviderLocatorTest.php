<?php

declare(strict_types=1);

namespace Unit\Slink\Storage\Infrastructure\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;
use Slink\Shared\Domain\Enum\StorageProvider;
use Slink\Storage\Domain\Service\StorageUsageProviderInterface;
use Slink\Storage\Infrastructure\Service\StorageUsageProviderLocator;

final class StorageUsageProviderLocatorTest extends TestCase {
    private ContainerInterface&MockObject $locator;
    private StorageUsageProviderLocator $providerLocator;

    protected function setUp(): void {
        $this->locator = $this->createMock(ContainerInterface::class);
        $this->providerLocator = new StorageUsageProviderLocator($this->locator);
    }

    #[Test]
    public function itGetsLocalProvider(): void {
        $provider = $this->createMock(StorageUsageProviderInterface::class);

        $this->locator
            ->expects($this->once())
            ->method('get')
            ->with('local')
            ->willReturn($provider);

        $result = $this->providerLocator->getProvider(StorageProvider::Local);

        $this->assertSame($provider, $result);
    }

    #[Test]
    public function itGetsS3Provider(): void {
        $provider = $this->createMock(StorageUsageProviderInterface::class);

        $this->locator
            ->expects($this->once())
            ->method('get')
            ->with('s3')
            ->willReturn($provider);

        $result = $this->providerLocator->getProvider(StorageProvider::AmazonS3);

        $this->assertSame($provider, $result);
    }

    #[Test]
    public function itGetsSmbProvider(): void {
        $provider = $this->createMock(StorageUsageProviderInterface::class);

        $this->locator
            ->expects($this->once())
            ->method('get')
            ->with('smb')
            ->willReturn($provider);

        $result = $this->providerLocator->getProvider(StorageProvider::SmbShare);

        $this->assertSame($provider, $result);
    }
}