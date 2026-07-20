<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Public;

use Slink\Shared\Infrastructure\Attribute\Groups;

#[Groups(['public'])]
final readonly class PublicCustomizationSettings {
  public function __construct(
    #[Groups(['public'])]
    public string $siteName = 'Slink',

    #[Groups(['public'])]
    public string $siteDescription = 'Fast and secure image sharing service',

    #[Groups(['public'])]
    public string $logoUrl = '',
  ) {}

  /**
   * @param array<string, mixed> $settings
   */
  public static function fromArray(array $settings): self {
    return new self(
      siteName: $settings['siteName'] ?? 'Slink',
      siteDescription: $settings['siteDescription'] ?? 'Fast and secure image sharing service',
      logoUrl: $settings['logoUrl'] ?? '',
    );
  }
}
