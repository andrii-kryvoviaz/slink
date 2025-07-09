<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Public;

use Slink\Shared\Infrastructure\Attribute\Groups;

#[Groups(['public'])]
final readonly class PublicAccessSettings {
  public function __construct(
    #[Groups(['public'])]
    public bool $allowGuestUploads,
    
    #[Groups(['public'])]
    public bool $allowUnauthenticatedAccess,
  ) {}
  
  /**
   * @param array<string, mixed> $settings
   */
  public static function fromArray(array $settings): self {
    return new self(
      $settings['allowGuestUploads'] ?? false,
      $settings['allowUnauthenticatedAccess'] ?? false
    );
  }
}
