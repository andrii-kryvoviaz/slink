<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject;

use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;

abstract readonly class AbstractSettingsValueObject extends AbstractCompoundValueObject {
  /**
   * @return SettingCategory
   */
  abstract function getSettingsCategory(): SettingCategory;
  
  /**
   * @return array<string, mixed>
   */
  public function toNormalizedPayload(): array {
    $flatten = function ($array, $prefix = '') use (&$flatten) {
      $result = [];
      foreach ($array as $key => $value) {
        $normalizedKey = implode('.', array_filter([$prefix, $key]));
        
        if (is_array($value)) {
          $result = array_merge($result, $flatten($value, $normalizedKey));
        } else {
          $result[$normalizedKey] = $value;
        }
      }
      return $result;
    };
    
    return $flatten($this->toPayload(), (string) $this);
  }
  
  /**
   * @param array<string, mixed> $payload
   * @param string $prefix
   * @return static
   */
  public static function fromNormalizedPayload(array $payload, string $prefix = ''): static {
    $result = [];
    
    foreach ($payload as $key => $value) {
      if ($prefix && str_starts_with($key, $prefix . '.')) {
        $key = substr($key, strlen($prefix) + 1);
      }
      
      $keys = explode('.', $key);
      $current = &$result;
      
      foreach ($keys as $part) {
        $current = &$current[$part];
      }
      
      $current = $value;
    }
    
    return static::fromPayload($result);
  }
  
  public function __toString(): string {
    return $this->getSettingsCategory()->getCategoryKey();
  }
}