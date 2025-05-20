<?php

declare(strict_types=1);

namespace Slink\Settings\Infrastructure\ReadModel\Repository;

use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\Enum\SettingType;
use Slink\Settings\Domain\Repository\SettingsRepositoryInterface;
use Slink\Settings\Infrastructure\ReadModel\View\SettingsView;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;

final class SettingsRepository extends AbstractRepository implements SettingsRepositoryInterface {
  
  /**
   * @param string $key
   * @param mixed $value
   * @param SettingCategory $category
   * @inheritDoc
   */
  #[\Override]
  public function set(string $key, mixed $value, SettingCategory $category): void {
    $setting = $this->findOneBy(['key' => $key]);
    
    $valueType = gettype($value);
    $valueTypeEnum = SettingType::from($valueType);
    $stringValue = match ($valueTypeEnum) {
      SettingType::Boolean => $value ? 'true' : 'false',
      default => (string) $value,
    };
    
    if (!$setting instanceof SettingsView) {
      $setting = new SettingsView($key, $stringValue, $valueTypeEnum, $category);
    } else {
      $setting->value = $stringValue;
      $setting->type = $valueTypeEnum;
      $setting->category = $category;
    }
    
    $this->_em->persist($setting);
  }
  
  /**
   * @param array<string, mixed> $settings
   * @param SettingCategory $category
   * @inheritDoc
   */
  #[\Override]
  public function setBulk(array $settings, SettingCategory $category): void {
    foreach ($settings as $key => $value) {
      $this->set($key, $value, $category);
    }
  }
  
  /**
   * @inheritDoc
   */
  #[\Override]
  public function get(string $key): ?SettingsView {
    $setting =  $this->findOneBy(['key' => $key]);
    
    if (!$setting) {
      return null;
    }
    
    if (!$setting instanceof SettingsView) {
      throw new \RuntimeException('Setting not found');
    }
    
    return $setting;
  }
  
  /**
   * @inheritDoc
   */
  #[\Override]
  public function all(): array {
    /** @var array<SettingsView> */
    return $this->findAll();
  }
  
  /**
   * @return string
   */
  #[\Override]
  static protected function entityClass(): string {
    return SettingsView::class;
  }
}