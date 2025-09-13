<?php

declare(strict_types=1);

namespace Unit\Slink\Storage\Application\Query\GetStorageUsage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Slink\Storage\Application\Query\GetStorageUsage\GetStorageUsageQuery;
use Slink\Storage\Application\Query\GetStorageUsage\GetStorageUsageQueryHandler;
use Slink\Storage\Domain\Service\StorageUsageServiceInterface;
use Slink\Storage\Domain\ValueObject\StorageUsage;

final class GetStorageUsageQueryHandlerTest extends TestCase {
    private StorageUsageServiceInterface&MockObject $storageUsageService;
    private GetStorageUsageQueryHandler $handler;

    protected function setUp(): void {
        $this->storageUsageService = $this->createMock(StorageUsageServiceInterface::class);
        $this->handler = new GetStorageUsageQueryHandler($this->storageUsageService);
    }

    #[Test]
    public function itHandlesGetStorageUsageQuery(): void {
        $query = new GetStorageUsageQuery();
        $storageUsage = new StorageUsage('local', 1024, 10240, 5);
        
        $expectedPayload = [
            'provider' => 'local',
            'usedBytes' => 1024,
            'totalBytes' => 10240,
            'fileCount' => 5,
            'usagePercentage' => 10.0,
        ];

        $this->storageUsageService
            ->expects($this->once())
            ->method('getCurrentUsage')
            ->willReturn($storageUsage);

        $result = $this->handler->__invoke($query);

        $this->assertSame($expectedPayload, $result);
    }

    #[Test]
    public function itHandlesGetStorageUsageQueryWithNullTotalBytes(): void {
        $query = new GetStorageUsageQuery();
        $storageUsage = new StorageUsage('s3', 2048, null, 8);
        
        $expectedPayload = [
            'provider' => 's3',
            'usedBytes' => 2048,
            'totalBytes' => null,
            'fileCount' => 8,
            'usagePercentage' => null,
        ];

        $this->storageUsageService
            ->expects($this->once())
            ->method('getCurrentUsage')
            ->willReturn($storageUsage);

        $result = $this->handler->__invoke($query);

        $this->assertSame($expectedPayload, $result);
    }
}