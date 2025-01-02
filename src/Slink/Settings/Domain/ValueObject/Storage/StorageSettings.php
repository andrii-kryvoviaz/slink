<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Storage;

use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\ValueObject\AbstractSettingsValueObject;
use Slink\Shared\Domain\Enum\StorageProvider;

final readonly class StorageSettings extends AbstractSettingsValueObject {
  private function __construct(
    private StorageProvider          $provider,
    private ?LocalStorageSettings    $localStorageSettings = null,
    private ?SmbStorageSettings      $smbStorageSettings = null,
    private ?AmazonS3StorageSettings $awsS3StorageSettings = null,
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
        StorageProvider::SmbShare->value => $this->smbStorageSettings?->toPayload(),
        StorageProvider::AmazonS3->value => $this->awsS3StorageSettings?->toPayload(),
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
    $awsS3StorageSettingsPayload = $payload['adapter'][StorageProvider::AmazonS3->value] ?? null;
    
    $localStorageSettings = $localStorageSettingsPayload
      ? LocalStorageSettings::fromPayload($localStorageSettingsPayload)
      : null;
    
    $smbStorageSettings = $smbStorageSettingsPayload
      ? SmbStorageSettings::fromPayload($smbStorageSettingsPayload)
      : null;
    
    $awsS3StorageSettings = $awsS3StorageSettingsPayload
      ? AmazonS3StorageSettings::fromPayload($awsS3StorageSettingsPayload)
      : null;
    
    return new self(
      $storageProvider,
      $localStorageSettings,
      $smbStorageSettings,
      $awsS3StorageSettings
    );
  }
  
  /**
   * @return SettingCategory
   */
  function getSettingsCategory(): SettingCategory {
    return SettingCategory::Storage;
  }
}