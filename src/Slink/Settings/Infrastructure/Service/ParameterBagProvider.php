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
   * @return bool
   */
  #[\Override]
  public function has(string $key): bool {
    return $this->get($key) !== null;
  }
  
  /**
   * @param string $key
   * @return mixed
   */
  #[\Override]
  public function get(string $key): mixed {
    if($this->parameterBag->has($key)) {
      return $this->parameterBag->get($key);
    }
    
    $keyParts = explode('.', $key);
    $firstKey = array_shift($keyParts) ?: '';
    
    if($this->parameterBag->has($firstKey)) {
      $value = $this->parameterBag->get($firstKey);
      
      foreach ($keyParts as $keyPart) {
        if(is_array($value) && array_key_exists($keyPart, $value)) {
          $value = $value[$keyPart];
        } else {
          return null;
        }
      }
      
      return $value;
    }
    
    return null;
  }
  
  /**
   * @param array<string> $keys
   * @return array<int, mixed>
   */
  #[\Override]
  public function getBulk(array $keys): array {
    return array_map(fn($key) => $this->get($key), $keys);
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
}