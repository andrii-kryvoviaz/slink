<?php

declare(strict_types=1);

namespace Unit\Slink\Storage\Infrastructure\Provider;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\Enum\StorageProvider;
use Slink\Storage\Domain\Exception\StorageDirectoryNotFoundException;
use Slink\Storage\Infrastructure\Provider\LocalStorageUsageProvider;

final class LocalStorageUsageProviderTest extends TestCase {
    private ConfigurationProviderInterface $configurationProvider;
    private LocalStorageUsageProvider $provider;

    protected function setUp(): void {
        $this->configurationProvider = $this->createStub(ConfigurationProviderInterface::class);
        $this->provider = new LocalStorageUsageProvider($this->configurationProvider);
    }

    #[Test]
    public function itSupportsLocalProvider(): void {
        $this->assertTrue($this->provider->supports(StorageProvider::Local));
        $this->assertFalse($this->provider->supports(StorageProvider::AmazonS3));
        $this->assertFalse($this->provider->supports(StorageProvider::SmbShare));
    }

    #[Test]
    public function itReturnsCorrectAlias(): void {
        $this->assertSame('local', LocalStorageUsageProvider::getAlias());
    }

    #[Test]
    public function itThrowsExceptionWhenStorageDirectoryIsNull(): void {
        $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $configurationProvider
            ->expects($this->once())
            ->method('get')
            ->with('storage.adapter.local.dir')
            ->willReturn(null);

        $provider = new LocalStorageUsageProvider($configurationProvider);

        $this->expectException(StorageDirectoryNotFoundException::class);
        $this->expectExceptionMessage('Storage directory not found or not accessible: unknown');

        $provider->getUsage();
    }

    #[Test]
    public function itThrowsExceptionWhenStorageDirectoryDoesNotExist(): void {
        $nonExistentDir = '/nonexistent/directory';

        $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $configurationProvider
            ->expects($this->once())
            ->method('get')
            ->with('storage.adapter.local.dir')
            ->willReturn($nonExistentDir);

        $provider = new LocalStorageUsageProvider($configurationProvider);

        $this->expectException(StorageDirectoryNotFoundException::class);
        $this->expectExceptionMessage("Storage directory not found or not accessible: {$nonExistentDir}");

        $provider->getUsage();
    }

    #[Test]
    public function itReturnsZeroUsageWhenSlinkDirectoryDoesNotExist(): void {
        $tempDir = sys_get_temp_dir() . '/slink_test_' . uniqid();
        mkdir($tempDir, 0755, true);

        try {
            $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
            $configurationProvider
                ->expects($this->once())
                ->method('get')
                ->with('storage.adapter.local.dir')
                ->willReturn($tempDir);

            $provider = new LocalStorageUsageProvider($configurationProvider);
            $usage = $provider->getUsage();

            $this->assertSame('local', $usage->getProvider());
            $this->assertSame(0, $usage->getUsedBytes());
            $this->assertSame(0, $usage->getFileCount());
            $this->assertIsInt($usage->getTotalBytes());
            $this->assertGreaterThan(0, $usage->getTotalBytes());
        } finally {
            rmdir($tempDir);
        }
    }

    #[Test]
    public function itCalculatesUsageWhenSlinkDirectoryExists(): void {
        $tempDir = sys_get_temp_dir() . '/slink_test_' . uniqid();
        $slinkDir = $tempDir . '/slink';
        mkdir($slinkDir, 0755, true);

        file_put_contents($slinkDir . '/file1.txt', 'content1');
        file_put_contents($slinkDir . '/file2.txt', 'content2');

        mkdir($slinkDir . '/subdir');
        file_put_contents($slinkDir . '/subdir/file3.txt', 'content3');

        try {
            $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
            $configurationProvider
                ->expects($this->once())
                ->method('get')
                ->with('storage.adapter.local.dir')
                ->willReturn($tempDir);

            $provider = new LocalStorageUsageProvider($configurationProvider);
            $usage = $provider->getUsage();

            $this->assertSame('local', $usage->getProvider());
            $this->assertSame(24, $usage->getUsedBytes());
            $this->assertSame(3, $usage->getFileCount());
            $this->assertIsInt($usage->getTotalBytes());
            $this->assertGreaterThan(0, $usage->getTotalBytes());
        } finally {
            unlink($slinkDir . '/subdir/file3.txt');
            rmdir($slinkDir . '/subdir');
            unlink($slinkDir . '/file1.txt');
            unlink($slinkDir . '/file2.txt');
            rmdir($slinkDir);
            rmdir($tempDir);
        }
    }
}