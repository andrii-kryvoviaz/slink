<?php

declare(strict_types=1);

namespace Slink\Settings\Application\Command\SaveSettings;

use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\Repository\SettingStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;

final readonly class SaveSettingsHandler implements CommandHandlerInterface {
  
  public function __construct(
    private SettingStoreRepositoryInterface $store
  ) {
  }
  
  public function __invoke(SaveSettingsCommand $command): void {
    $settings = $this->store->get();
    
    $category = SettingCategory::from($command->getCategory());
    $data = $category->createSettingsCategoryRoot($command->getSettings());
    
    $settings->setSettings($data);
    
    $this->store->store($settings);
  }
}