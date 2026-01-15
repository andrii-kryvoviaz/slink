<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Public;

use Slink\Shared\Infrastructure\Attribute\Groups;

#[Groups(['public'])]
final readonly class PublicImageSettings {
  public function __construct(
    #[Groups(['public'])]
    public bool $enableLicensing = false,
    
    #[Groups(['public'])]
    public bool $allowOnlyPublicImages = false,
  ) {}
  
  /**
   * @param array<string, mixed> $settings
   */
  public static function fromArray(array $settings): self {
    return new self(
      $settings['enableLicensing'] ?? false,
      $settings['allowOnlyPublicImages'] ?? false,
    );
  }
}
