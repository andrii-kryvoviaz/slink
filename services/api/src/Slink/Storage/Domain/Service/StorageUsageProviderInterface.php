<?php

declare(strict_types=1);

namespace Slink\Storage\Domain\Service;

use Slink\Shared\Domain\Enum\StorageProvider;
use Slink\Storage\Domain\ValueObject\StorageUsage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
interface StorageUsageProviderInterface {
  public function getUsage(): StorageUsage;
  
  public function supports(StorageProvider $provider): bool;
  
  public static function getAlias(): string;
}