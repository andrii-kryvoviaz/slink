<?php

declare(strict_types=1);

namespace Slink\Settings\Application\Command\SaveSettings;

use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\Exception\SsoProviderRequiredException;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Settings\Domain\Repository\SettingStoreRepositoryInterface;
use Slink\Settings\Domain\Specification\SignInMethodSpecificationInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;

final readonly class SaveSettingsHandler implements CommandHandlerInterface {
  private const string LOCAL_AUTH_ENABLED_KEY = 'localAuthEnabled';
  private const array CUSTOMIZATION_THEMING_KEYS = ['themeSelectorEnabled', 'defaultTheme', 'hiddenThemes', 'themes'];

  /**
   * @param SettingStoreRepositoryInterface $store
   * @param ConfigurationProviderInterface<SettingsService> $configurationProvider
   */
  public function __construct(
    private SettingStoreRepositoryInterface $store,
    private ConfigurationProviderInterface $configurationProvider,
    private SignInMethodSpecificationInterface $signInMethodSpecification,
  ) {
  }
  
  public function __invoke(SaveSettingsCommand $command): void {
    $settings = $this->store->get();
    
    $category = SettingCategory::from($command->getCategory());
    
    $data = $category->createSettingsCategoryRoot(
      $this->withEffectiveCustomization($category, $this->withEffectiveLocalAuth($category, $command->getSettings()))
    );
    
    if (!$this->signInMethodSpecification->isSatisfiedBy($data)) {
      throw new SsoProviderRequiredException();
    }
    
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

  /**
   * @param array<string, mixed> $settings
   * @return array<string, mixed>
   */
  private function withEffectiveCustomization(SettingCategory $category, array $settings): array {
    if ($category !== SettingCategory::Customization) {
      return $settings;
    }

    foreach (self::CUSTOMIZATION_THEMING_KEYS as $key) {
      if (($settings[$key] ?? null) !== null) {
        continue;
      }

      $effective = $this->configurationProvider->get($category->getCategoryKey() . '.' . $key);

      if ($effective !== null) {
        $settings[$key] = $effective;
      }
    }

    return $settings;
  }
}
