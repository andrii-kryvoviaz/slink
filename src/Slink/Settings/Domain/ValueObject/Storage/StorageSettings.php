<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Storage;

use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\Enum\StorageProvider;
use Slink\Settings\Domain\ValueObject\AbstractSettingsValueObject;
use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;

final readonly class StorageSettings extends AbstractSettingsValueObject {
  private function __construct(
    private StorageProvider              $provider,
    private ?LocalStorageSettings        $localStorageSettings = null,
    private ?SmbStorageSettings          $smbStorageSettings = null
  ) {
  }
  
  /**
   * @return array<string, mixed>
   */
  #[\Override]
  public function toPayload(): array {
    return [
      'provider' => $this->provider->value,
      'adapter' => [
        StorageProvider::Local->value => $this->localStorageSettings?->toPayload(),
        StorageProvider::SmbShare->value => $this->smbStorageSettings?->toPayload()
      ]
    ];
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return static
   */
  #[\Override]
  public static function fromPayload(array $payload): static {
    $storageProvider = StorageProvider::from($payload['provider']);
    
    $localStorageSettingsPayload = $payload['adapter'][StorageProvider::Local->value] ?? null;
    $smbStorageSettingsPayload = $payload['adapter'][StorageProvider::SmbShare->value] ?? null;
    
    $localStorageSettings = $localStorageSettingsPayload
      ? LocalStorageSettings::fromPayload($localStorageSettingsPayload)
      : null;
    
    $smbStorageSettings = $smbStorageSettingsPayload
      ? SmbStorageSettings::fromPayload($smbStorageSettingsPayload)
      : null;
    
    return new self(
      $storageProvider,
      $localStorageSettings,
      $smbStorageSettings
    );
  }
  
  /**
   * @return SettingCategory
   */
  function getSettingsCategory(): SettingCategory {
    return SettingCategory::Storage;
  }
}