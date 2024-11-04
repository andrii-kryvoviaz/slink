<?php

declare(strict_types=1);

namespace Slink\Settings\Application\Service;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slink\Settings\Domain\Enum\ConfigurationProvider;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Settings\Infrastructure\Provider\ConfigurationProviderLocator;

final readonly class SettingsService implements ConfigurationProviderInterface {
  private ConfigurationProviderInterface $configurationProvider;
  private ConfigurationProviderInterface $fallbackProvider;
  
  /**
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
   */
  public function __construct(
    ConfigurationProviderLocator $configurationLocator
  ) {
    $this->configurationProvider = $configurationLocator->get(ConfigurationProvider::Store);
    $this->fallbackProvider = $configurationLocator->get(ConfigurationProvider::Default);
  }
  
  /**
   * @param string $key
   * @return mixed
   */
  public function get(string $key): mixed {
    return $this->configurationProvider->get($key) ?? $this->fallbackProvider->get($key);
  }
  
  /**
   * @param string $key
   * @return bool
   */
  public function has(string $key): bool {
    return $this->configurationProvider->has($key) || $this->fallbackProvider->has($key);
  }
  
  /**
   * @return array<string, mixed>
   */
  public function all(): array {
    return array_replace_recursive($this->fallbackProvider->all(), $this->configurationProvider->all());
  }
}