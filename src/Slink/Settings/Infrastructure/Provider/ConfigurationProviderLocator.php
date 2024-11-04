<?php

declare(strict_types=1);

namespace Slink\Settings\Infrastructure\Provider;


use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slink\Settings\Domain\Enum\ConfigurationProvider;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

final readonly class ConfigurationProviderLocator {
  
  public function __construct(
    #[AutowireLocator(ConfigurationProviderInterface::class, defaultIndexMethod: 'getIndexName')]
    private ContainerInterface $container,
  ) {}
  
  /**
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
   */
  public function get(ConfigurationProvider $provider): ConfigurationProviderInterface {
    return $this->container->get($provider->value);
  }
}