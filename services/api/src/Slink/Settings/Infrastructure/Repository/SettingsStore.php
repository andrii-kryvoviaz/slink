<?php

declare(strict_types=1);

namespace Slink\Settings\Infrastructure\Repository;

use Slink\Settings\Domain\Repository\SettingStoreRepositoryInterface;
use Slink\Settings\Domain\Settings;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\EventStore\AbstractStoreRepository;

final class SettingsStore extends AbstractStoreRepository implements SettingStoreRepositoryInterface {
  /**
   * @return string
   */
  #[\Override]
  static function getAggregateRootClass(): string {
    return Settings::class;
  }
  
  /**
   * @return Settings
   */
  #[\Override]
  public function get(): Settings {
    $id = ID::fromString(Settings::getIdReference());
    
    $settings = $this->retrieve($id);
    
    if(!$settings instanceof Settings) {
      throw new \RuntimeException(sprintf('Settings with id %s not found', $id->toString()));
    }
    
    return $settings;
  }
  
  /**
   * @param Settings $settings
   * @return void
   */
  #[\Override]
  public function store(Settings $settings): void {
    $this->persist($settings);
  }
}