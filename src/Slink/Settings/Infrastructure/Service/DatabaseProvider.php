<?php

declare(strict_types=1);

namespace Slink\Settings\Infrastructure\Service;

use Slink\Settings\Domain\Enum\SettingType;
use Slink\Settings\Domain\Repository\SettingsRepositoryInterface;
use Slink\Settings\Domain\Service\ConfigurationProvider;

final class DatabaseProvider implements ConfigurationProvider {
  /**
   * @var array<string, mixed>
   */
  private array $settings = [];
  
  /**
   * @param SettingsRepositoryInterface $settingsRepository
   */
  public function __construct(SettingsRepositoryInterface $settingsRepository) {
    $allSavedEntries = $settingsRepository->all();
    
    foreach ($allSavedEntries as $entry) {
      $this->settings[$entry->key] = $this->convertValueType($entry->value, $entry->type);
    }
  }
  
  /**
   * @param mixed $value
   * @param string $type
   * @return mixed
   */
  private function convertValueType(mixed $value, string $type): mixed {
    $type = SettingType::tryFrom($type);
    
    return match ($type) {
      SettingType::String => (string) $value,
      SettingType::Integer => (int) $value,
      SettingType::Float => (float) $value,
      SettingType::Boolean => (bool) $value,
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
    return $this->settings[$key] ?? null;
  }
  
  /**
   * @param string $key
   * @param mixed $value
   * @return void
   */
  #[\Override]
  public function set(string $key, mixed $value): void {
    $this->settings[$key] = $value;
  }
  
  /**
   * @param string $key
   * @return bool
   */
  #[\Override]
  public function has(string $key): bool {
    return isset($this->settings[$key]);
  }
}