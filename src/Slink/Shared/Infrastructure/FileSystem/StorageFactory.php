<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\FileSystem;

use Slink\Image\Domain\Service\ImageTransformerInterface;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Infrastructure\FileSystem\Storage\AbstractStorage;
use Slink\Shared\Infrastructure\FileSystem\Storage\StorageInterface;

final readonly class StorageFactory {
  
  public function __construct(
    private ConfigurationProviderInterface $configurationProvider,
    private ImageTransformerInterface      $imageTransformer
  ) {
  }
  
  /**
   * @param array<string, mixed> $storageOptions
   * @return StorageInterface
   */
  public function create(array $storageOptions): StorageInterface {
    $storageProvider = $this->configurationProvider->get('storage.provider');
    
    $storageProviderClass = $storageOptions[$storageProvider] ?? null;
    
    if (is_subclass_of($storageProviderClass, AbstractStorage::class)) {
      $storageProvider = $storageProviderClass::create($this->configurationProvider);
      
      $storageProvider->setImageTransformer($this->imageTransformer);
      
      return $storageProvider;
    }
    
    $message = sprintf('Invalid Storage provider `%s`', $storageProvider);
    
    throw new \InvalidArgumentException($message);
  }
}