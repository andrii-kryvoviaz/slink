<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\ValueObject\AbstractSettingsValueObject;

final readonly class SettingsChanged implements SerializablePayload {
  /**
   * @param SettingCategory $category
   * @param AbstractSettingsValueObject $settings
   */
  public function __construct(
    public SettingCategory $category,
    public AbstractSettingsValueObject $settings
  ) {
  }
  
  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'category' => $this->category->value,
      'settings' => $this->settings->toNormalizedPayload(),
    ];
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return static
   */
  public static function fromPayload(array $payload): static {
    $settingCategory = SettingCategory::from($payload['category']);
    
    /** @var class-string<AbstractSettingsValueObject> $settingsClass */
    $settingsClass = $settingCategory->getSettingsCategoryRootClass();
    $settingsPayload = $settingsClass::fromNormalizedPayload($payload['settings'], $payload['category']);
    
    return new self(
      $settingCategory,
      $settingsPayload,
    );
  }
}