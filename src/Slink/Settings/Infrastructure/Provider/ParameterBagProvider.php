<?php

declare(strict_types=1);

namespace Slink\Settings\Infrastructure\Provider;

use Slink\Settings\Domain\Enum\ConfigurationProvider;
use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Infrastructure\DependencyInjection\ServiceLocator\Indexable;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final readonly class ParameterBagProvider implements ConfigurationProviderInterface, Indexable {
  
  public function __construct(
    private ParameterBagInterface $parameterBag
  ) {
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
   * @return array<string, mixed>
   */
  public function all(): array {
    return array_filter($this->parameterBag->all(),
      fn($key) => in_array($key, SettingCategory::values()),
      ARRAY_FILTER_USE_KEY
    );
  }
  
  /**
   * @return string
   */
  public static function getIndexName(): string {
    return (ConfigurationProvider::Default)->value;
  }
}