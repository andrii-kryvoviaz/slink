<?php

declare(strict_types=1);

namespace Slink\Settings\Infrastructure\Service;

use Slink\Settings\Domain\Service\ConfigurationProvider;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final readonly class ParameterBagProvider implements ConfigurationProvider {
  
  public function __construct(private ParameterBagInterface $parameterBag) {
  }
  
  /**
   * @param string $key
   * @return mixed
   */
  #[\Override]
  public function get(string $key): mixed {
    return $this->parameterBag->get($key);
  }
  
  /**
   * @param string $key
   * @param mixed $value
   * @return void
   */
  #[\Override]
  public function set(string $key, mixed $value): void {
    $this->parameterBag->set($key, $value);
  }
  
  /**
   * @param string $key
   * @return bool
   */
  #[\Override]
  public function has(string $key): bool {
    return $this->parameterBag->has($key);
  }
}