<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\FileSystem;

use Slik\Shared\Infrastructure\FileSystem\Storage\StorageInterface;

final readonly class StorageFactory {
  
  public function create(array $storageOptions, string $storageProvider = 'local'): StorageInterface {
    $storageProviderConfig = $storageOptions[$storageProvider] ?? null;
    
    if ($storageProviderConfig) {
      $storageProviderClass = $storageProviderConfig['class'];
      $storageProviderArguments = $storageProviderConfig['arguments'] ?? [];
      
      return new $storageProviderClass(...$storageProviderArguments);
    }
    
    $message = sprintf('Invalid Storage provider `%s`', $storageProvider);
    
    throw new \InvalidArgumentException($message);
  }
}