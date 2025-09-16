<?php

declare(strict_types=1);

namespace Slink\Settings\Infrastructure\Repository;

use Slink\Settings\Domain\Repository\SettingStoreRepositoryInterface;
use Slink\Settings\Domain\Settings;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\EventStore\AbstractSnapshotStoreRepository;

final class SettingsStore extends AbstractSnapshotStoreRepository implements SettingStoreRepositoryInterface {
  protected static function getAggregateRootClass(): string {
    return Settings::class;
  }
  
  public function get(): Settings {
    $id = ID::fromString(Settings::getIdReference());
    
    $settings = $this->retrieve($id);
    
    if(!$settings instanceof Settings) {
      throw new \RuntimeException(sprintf('Settings with id %s not found', $id->toString()));
    }
    
    return $settings;
  }
  
  public function store(Settings $settings): void {
    $this->persist($settings);
  }
}