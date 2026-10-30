<?php

declare(strict_types=1);

namespace Slink\Settings\Application\Command\SaveSettings;

use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Settings\Domain\Repository\SettingStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;

final readonly class SaveSettingsHandler implements CommandHandlerInterface {
  private const string LOCAL_AUTH_ENABLED_KEY = 'localAuthEnabled';
  
  /**
   * @param SettingStoreRepositoryInterface $store
   * @param ConfigurationProviderInterface<SettingsService> $configurationProvider
   */
  public function __construct(
    private SettingStoreRepositoryInterface $store,
    private ConfigurationProviderInterface $configurationProvider,
  ) {
  }
  
  public function __invoke(SaveSettingsCommand $command): void {
    $settings = $this->store->get();
    
    $category = SettingCategory::from($command->getCategory());
    $data = $category->createSettingsCategoryRoot($this->withEffectiveLocalAuth($category, $command->getSettings()));
    
    $settings->setSettings($data);
    
    $this->store->store($settings);
  }
  
  /**
   * @param array<string, mixed> $settings
   * @return array<string, mixed>
   */
  private function withEffectiveLocalAuth(SettingCategory $category, array $settings): array {
    if ($category !== SettingCategory::User) {
      return $settings;
    }
    
    $settings[self::LOCAL_AUTH_ENABLED_KEY] ??= $this->configurationProvider->get(
      $category->getCategoryKey() . '.' . self::LOCAL_AUTH_ENABLED_KEY
    );
    
    return $settings;
  }
}
