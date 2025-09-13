<?php

declare(strict_types=1);

namespace Slink\Storage\Domain\Service;

use Slink\Shared\Domain\Enum\StorageProvider;

interface StorageUsageProviderLocatorInterface {
  public function getProvider(StorageProvider $providerType): StorageUsageProviderInterface;
}
