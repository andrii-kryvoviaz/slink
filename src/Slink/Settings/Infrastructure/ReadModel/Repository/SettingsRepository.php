<?php

declare(strict_types=1);

namespace Slink\Settings\Infrastructure\ReadModel\Repository;

use Slink\Settings\Domain\Repository\SettingsRepositoryInterface;
use Slink\Settings\Infrastructure\ReadModel\View\SettingsView;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;

final class SettingsRepository extends AbstractRepository implements SettingsRepositoryInterface {
  
  /**
   * @inheritDoc
   */
  #[\Override]
  public function set(string $key, mixed $value): void {
    $setting = $this->findOneBy(['key' => $key]);
    
    $valueType = gettype($value);
    
    if (!$setting instanceof SettingsView) {
      $setting = new SettingsView($key, $value, $valueType);
    } else {
      $setting->value = $value;
      $setting->type = $valueType;
    }
    
    $this->_em->persist($setting);
  }
  
  /**
   * @inheritDoc
   */
  #[\Override]
  public function setBulk(array $settings): void {
    foreach ($settings as $key => $value) {
      $this->set($key, $value);
    }
  }
  
  /**
   * @inheritDoc
   */
  #[\Override]
  public function get(string $key): SettingsView {
    $setting =  $this->findOneBy(['key' => $key]);
    
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