<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\FileSystem;

use Slink\Settings\Domain\Service\ConfigurationProvider;
use Slink\Shared\Infrastructure\FileSystem\Storage\StorageInterface;

final readonly class StorageFactory {
  
  public function __construct(
    private ConfigurationProvider $configurationProvider
  ) {
  }
  
  /**
   * @param array<string, mixed> $storageOptions
   * @return StorageInterface
   */
  public function create(array $storageOptions): StorageInterface {
    $storageProvider = $this->configurationProvider->get('storage.provider');
    
    $storageProviderClass = $storageOptions[$storageProvider] ?? null;
    
    if (is_subclass_of($storageProviderClass, StorageInterface::class)) {
      return $storageProviderClass::create($this->configurationProvider);
    }
    
    $message = sprintf('Invalid Storage provider `%s`', $storageProvider);
    
    throw new \InvalidArgumentException($message);
  }
}