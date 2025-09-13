<?php

declare(strict_types=1);

namespace Slink\Storage\Domain\Service;

use Slink\Storage\Domain\ValueObject\StorageUsage;

interface StorageUsageServiceInterface {
  public function getCurrentUsage(): StorageUsage;
}