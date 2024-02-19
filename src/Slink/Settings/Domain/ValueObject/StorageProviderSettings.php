<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject;

use Slink\Settings\Domain\Enum\StorageProvider;
use Slink\Settings\Domain\Exception\InvalidSettingsException;
use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;

final readonly class StorageProviderSettings extends AbstractCompoundValueObject {
  private function __construct(
    private StorageProvider $type,
    private ?AbstractCompoundValueObject $storageProviderSettings
  ) {
    $this->validate();
  }
  
  /**
   * @return void
   */
  private function validate(): void {
    if($this->type->equals(StorageProvider::Local) && !$this->storageProviderSettings instanceof LocalStorageSettings) {
      throw new InvalidSettingsException(sprintf('Invalid storage provider settings for %s', $this->type->value));
    }
    
    if($this->type->equals(StorageProvider::SmbShare) && !$this->storageProviderSettings instanceof SmbStorageSettings) {
      throw new InvalidSettingsException(sprintf('Invalid storage provider settings for %s', $this->type->value));
    }
  }
  
  /**
   * @return array<string, mixed>
   */
  #[\Override]
  public function toPayload(): array {
    return [
      'type' => $this->type->value,
      ...($this->storageProviderSettings?->toPayload() ?? [])
    ];
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return static
   */
  #[\Override]
  public static function fromPayload(array $payload): static {
    $storageProvider = StorageProvider::from($payload['type']);
    $settingsClass = StorageProvider::getSettingsClass($storageProvider);
    
    if(!class_exists($settingsClass)) {
      throw new \InvalidArgumentException('Invalid storage provider type');
    }
    
    return new self(
      $storageProvider,
      $settingsClass::fromPayload($payload)
    );
  }
}