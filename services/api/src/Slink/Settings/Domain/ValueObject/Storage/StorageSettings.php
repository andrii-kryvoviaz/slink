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
    $adapter = array_filter([
      StorageProvider::Local->value => $this->localStorageSettings?->toPayload(),
      StorageProvider::SmbShare->value => $this->smbStorageSettings?->toPayload(),
      StorageProvider::AmazonS3->value => $this->awsS3StorageSettings?->toPayload(),
    ], static fn (?array $value): bool => $value !== null);

    return [
      'provider' => $this->provider->value,
      'adapter' => $adapter,
    ];
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return static
   */
  #[\Override]
  public static function fromPayload(array $payload): static {
    $provider = StorageProvider::from($payload['provider']);
    $config = $payload['adapter'][$provider->value] ?? null;

    return match ($provider) {
      StorageProvider::Local => new self(
        $provider,
        localStorageSettings: $config ? LocalStorageSettings::fromPayload($config) : null,
      ),
      StorageProvider::SmbShare => new self(
        $provider,
        smbStorageSettings: $config ? SmbStorageSettings::fromPayload($config) : null,
      ),
      StorageProvider::AmazonS3 => new self(
        $provider,
        awsS3StorageSettings: $config ? AmazonS3StorageSettings::fromPayload($config) : null,
      ),
    };
  }
  
  /**
   * @return SettingCategory
   */
  function getSettingsCategory(): SettingCategory {
    return SettingCategory::Storage;
  }
}