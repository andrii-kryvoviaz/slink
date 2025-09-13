<?php

declare(strict_types=1);

namespace Slink\Storage\Application\Service;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\Enum\StorageProvider;
use Slink\Storage\Domain\Exception\StorageProviderNotConfiguredException;
use Slink\Storage\Domain\Service\StorageUsageServiceInterface;
use Slink\Storage\Domain\ValueObject\StorageUsage;
use Slink\Storage\Domain\Service\StorageUsageProviderLocatorInterface;

final readonly class StorageUsageService implements StorageUsageServiceInterface {
  public function __construct(
    private StorageUsageProviderLocatorInterface $providerLocator,
    private ConfigurationProviderInterface $configurationProvider
  ) {
  }
  
  public function getCurrentUsage(): StorageUsage {
    $currentProvider = $this->configurationProvider->get('storage.provider');
    
    if (!$currentProvider) {
      throw new StorageProviderNotConfiguredException();
    }
    
    $storageProvider = StorageProvider::from($currentProvider);
    $provider = $this->providerLocator->getProvider($storageProvider);
    return $provider->getUsage();
  }
}