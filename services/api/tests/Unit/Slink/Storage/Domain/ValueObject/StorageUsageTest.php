<?php

declare(strict_types=1);

namespace Unit\Slink\Storage\Domain\ValueObject;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Storage\Domain\ValueObject\StorageUsage;

final class StorageUsageTest extends TestCase {
    #[Test]
    public function itCreatesStorageUsageWithMinimalData(): void {
        $storageUsage = new StorageUsage(
            provider: 'local',
            usedBytes: 1024
        );

        $this->assertSame('local', $storageUsage->getProvider());
        $this->assertSame(1024, $storageUsage->getUsedBytes());
        $this->assertNull($storageUsage->getTotalBytes());
        $this->assertSame(0, $storageUsage->getFileCount());
        $this->assertNull($storageUsage->getUsagePercentage());
    }

    #[Test]
    public function itCreatesStorageUsageWithFullData(): void {
        $storageUsage = new StorageUsage(
            provider: 's3',
            usedBytes: 2048,
            totalBytes: 10240,
            fileCount: 15
        );

        $this->assertSame('s3', $storageUsage->getProvider());
        $this->assertSame(2048, $storageUsage->getUsedBytes());
        $this->assertSame(10240, $storageUsage->getTotalBytes());
        $this->assertSame(15, $storageUsage->getFileCount());
        $this->assertSame(20.0, $storageUsage->getUsagePercentage());
    }

    #[Test]
    public function itCalculatesUsagePercentageCorrectly(): void {
        $storageUsage = new StorageUsage(
            provider: 'local',
            usedBytes: 7500,
            totalBytes: 10000
        );

        $this->assertSame(75.0, $storageUsage->getUsagePercentage());
    }

    #[Test]
    public function itReturnsNullUsagePercentageWhenTotalBytesIsZero(): void {
        $storageUsage = new StorageUsage(
            provider: 'local',
            usedBytes: 1024,
            totalBytes: 0
        );

        $this->assertNull($storageUsage->getUsagePercentage());
    }

    #[Test]
    public function itReturnsNullUsagePercentageWhenTotalBytesIsNull(): void {
        $storageUsage = new StorageUsage(
            provider: 'local',
            usedBytes: 1024,
            totalBytes: null
        );

        $this->assertNull($storageUsage->getUsagePercentage());
    }

    #[Test]
    public function itConvertsToPayloadCorrectly(): void {
        $storageUsage = new StorageUsage(
            provider: 'smb',
            usedBytes: 5120,
            totalBytes: 20480,
            fileCount: 42,
            cacheBytes: 0,
            cacheFileCount: 0
        );

        $expectedPayload = [
            'provider' => 'smb',
            'usedBytes' => 5120,
            'totalBytes' => 20480,
            'fileCount' => 42,
            'usagePercentage' => 25.0,
            'cacheBytes' => 0,
            'cacheFileCount' => 0,
        ];

        $this->assertSame($expectedPayload, $storageUsage->toPayload());
    }

    #[Test]
    public function itConvertsToPayloadWithNullValues(): void {
        $storageUsage = new StorageUsage(
            provider: 'local',
            usedBytes: 1024
        );

        $expectedPayload = [
            'provider' => 'local',
            'usedBytes' => 1024,
            'totalBytes' => null,
            'fileCount' => 0,
            'usagePercentage' => null,
            'cacheBytes' => 0,
            'cacheFileCount' => 0,
        ];

        $this->assertSame($expectedPayload, $storageUsage->toPayload());
    }
}