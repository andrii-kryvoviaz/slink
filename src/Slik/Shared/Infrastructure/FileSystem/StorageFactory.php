<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\FileSystem;

use Slik\Shared\Infrastructure\FileSystem\Storage\StorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final readonly class StorageFactory {
  
  /**
   * @param ContainerInterface $container
   */
  public function __construct(private ContainerInterface $container) {
  }
  
  /**
   * @param array<string, mixed> $storageOptions
   * @param string $storageProvider
   * @return StorageInterface
   */
  public function create(array $storageOptions, string $storageProvider = 'local'): StorageInterface {
    $storageProviderConfig = $storageOptions[$storageProvider] ?? null;
    
    if ($storageProviderConfig) {
      $storageProviderClass = $storageProviderConfig['class'];
      
      $storageProviderArguments = array_map(function($argument) {
        if(is_string($argument) && $this->container->has($argument)) {
          return $this->container->get($argument);
        }
        
        return $argument;
      }, $storageProviderConfig['arguments'] ?? []);
      
      return new $storageProviderClass(...$storageProviderArguments); // @phpstan-ignore-line
    }
    
    $message = sprintf('Invalid Storage provider `%s`', $storageProvider);
    
    throw new \InvalidArgumentException($message);
  }
}