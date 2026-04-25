<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service;

use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;

final readonly class ImageVisibilityResolver {
  /**
   * @param ConfigurationProviderInterface<SettingsService> $configurationProvider
   */
  public function __construct(
    private ConfigurationProviderInterface $configurationProvider,
  ) {
  }

  public function resolveListIsPublicFilter(): ?bool {
    if ($this->configurationProvider->get('image.allowOnlyPublicImages')) {
      return null;
    }

    return true;
  }
}
