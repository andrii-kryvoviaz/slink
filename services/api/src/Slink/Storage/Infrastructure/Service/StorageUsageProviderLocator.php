<?php

declare(strict_types=1);

namespace Slink\Storage\Infrastructure\Service;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slink\Shared\Domain\Enum\StorageProvider;
use Slink\Storage\Domain\Service\StorageUsageProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

final readonly class StorageUsageProviderLocator implements \Slink\Storage\Domain\Service\StorageUsageProviderLocatorInterface {
  public function __construct(
    #[AutowireLocator(StorageUsageProviderInterface::class, defaultIndexMethod: 'getAlias')]
    private ContainerInterface $locator
  ) {
  }
  
  /**
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
   */
  public function getProvider(StorageProvider $providerType): StorageUsageProviderInterface {
    return $this->locator->get($providerType->value);
  }
}