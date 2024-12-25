<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\FileSystem;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slink\Shared\Domain\Enum\StorageProvider;

use Slink\Shared\Infrastructure\FileSystem\Storage\StorageInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

final readonly class StorageProviderLocator {
  public function __construct(
    #[AutowireLocator(StorageInterface::class, defaultIndexMethod: 'getAlias')]
    private ContainerInterface $locator
  ) {
  }
  
  /**
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
   */
  public function get(StorageProvider $provider): mixed {
    return $this->locator->get($provider->value);
  }
}