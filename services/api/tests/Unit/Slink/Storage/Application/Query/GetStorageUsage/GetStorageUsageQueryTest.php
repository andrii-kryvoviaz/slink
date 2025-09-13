<?php

declare(strict_types=1);

namespace Unit\Slink\Storage\Application\Query\GetStorageUsage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Storage\Application\Query\GetStorageUsage\GetStorageUsageQuery;

final class GetStorageUsageQueryTest extends TestCase {
    #[Test]
    public function itCanBeCreated(): void {
        $query = new GetStorageUsageQuery();

        $this->assertInstanceOf(GetStorageUsageQuery::class, $query);
    }
}