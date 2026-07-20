<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Public;

use Slink\Settings\Domain\ValueObject\Customization\CustomizationSettings;
use Slink\Shared\Infrastructure\Attribute\Groups;

#[Groups(['public'])]
final readonly class PublicCustomizationSettings {
  public function __construct(
    #[Groups(['public'])]
    public string $siteName,

    #[Groups(['public'])]
    public string $siteDescription,

    #[Groups(['public'])]
    public string $logoUrl,
  ) {}

  /**
   * @param array<string, mixed> $settings
   */
  public static function fromArray(array $settings): self {
    return new self(
      siteName: $settings['siteName'] ?? CustomizationSettings::DEFAULT_SITE_NAME,
      siteDescription: $settings['siteDescription'] ?? CustomizationSettings::DEFAULT_SITE_DESCRIPTION,
      logoUrl: $settings['logoUrl'] ?? '',
    );
  }
}
