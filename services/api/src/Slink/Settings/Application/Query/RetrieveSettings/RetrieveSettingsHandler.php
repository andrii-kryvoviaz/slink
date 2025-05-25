<?php

declare(strict_types=1);

namespace Slink\Settings\Application\Query\RetrieveSettings;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slink\Settings\Domain\Enum\ConfigurationProvider;
use Slink\Settings\Infrastructure\Provider\ConfigurationProviderLocator;
use Slink\Shared\Application\Query\QueryHandlerInterface;

final readonly class RetrieveSettingsHandler implements QueryHandlerInterface {
  public function __construct(
    private ConfigurationProviderLocator $configurationLocator
  ) {
  }
  
  /**
   * @param RetrieveSettingsQuery $query
   * @return array<string, mixed>
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
   */
  public function __invoke(RetrieveSettingsQuery $query): array {
    $configurationProvider = ConfigurationProvider::tryFrom($query->getProvider());
    
    if ($configurationProvider === null) {
      throw new \InvalidArgumentException('Invalid configuration provider');
    }
    
    $service = $this->configurationLocator->get($configurationProvider);
    
    return $service->all();
  }
}