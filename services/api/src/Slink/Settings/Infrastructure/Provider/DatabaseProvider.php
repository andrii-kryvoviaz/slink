<?php

declare(strict_types=1);

namespace Slink\Settings\Infrastructure\Provider;

use Slink\Settings\Domain\Enum\ConfigurationProvider;
use Slink\Settings\Domain\Enum\SettingType;
use Slink\Settings\Domain\Repository\SettingsRepositoryInterface;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Infrastructure\DependencyInjection\ServiceLocator\Indexable;
use Slink\Shared\Infrastructure\Encryption\EncryptionRegistry;

/**
 * @template-implements ConfigurationProviderInterface<DatabaseProvider>
 */
final readonly class DatabaseProvider implements ConfigurationProviderInterface, Indexable {
  /**
   * @param SettingsRepositoryInterface $settingsRepository
   */
  public function __construct(private SettingsRepositoryInterface $settingsRepository) {
  }
  
  /**
   * @param mixed $value
   * @param string $type
   * @return mixed
   */
  private function convertValueType(mixed $value, string $type): mixed {
    $type = SettingType::tryFrom($type);
    
    return match ($type) {
      SettingType::String => EncryptionRegistry::decrypt((string) $value),
      SettingType::Integer => (int) $value,
      SettingType::Float => (float) $value,
      SettingType::Boolean => filter_var($value, FILTER_VALIDATE_BOOLEAN),
      SettingType::Serialized => unserialize($value),
      SettingType::Null => null,
      default => $value,
    };
  }
  
  /**
   * @param string $key
   * @return mixed
   */
  #[\Override]
  public function get(string $key): mixed {
    $keyParts = $this->parseKey($key);
    $entries = $this->all();
    
    foreach ($keyParts as $keyPart) {
      if (!array_key_exists($keyPart, $entries)) {
        return null;
      }
      
      $entries = $entries[$keyPart];
    }
    
    return $entries;
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
   * @return array<string, mixed>
   */
  public function all(): array {
    $settings = $this->settingsRepository->all();
    
    $nestedSettings = [];
    
    foreach ($settings as $entry) {
      $keyParts = $this->parseKey($entry->key);
      $lastKey = array_pop($keyParts) ?: '';
      $current = &$nestedSettings;
      
      foreach ($keyParts as $keyPart) {
        $current = &$current[$keyPart];
      }
      
      $current[$lastKey] = $this->convertValueType($entry->value, $entry->type->value);
    }
    
    return $nestedSettings;
  }
  
  /**
   * @param string $key
   * @return array<string>
   */
  private function parseKey(string $key): array {
    return explode('.', $key);
  }
  
  /**
   * @return string
   */
  public static function getIndexName(): string {
    return (ConfigurationProvider::Store)->value;
  }
}