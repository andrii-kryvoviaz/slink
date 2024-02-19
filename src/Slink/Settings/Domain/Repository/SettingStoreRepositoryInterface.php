<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Repository;

use Slink\Settings\Domain\Settings;

interface SettingStoreRepositoryInterface {
  
  public function get(): Settings;
  
  public function store(Settings $settings): void;
}