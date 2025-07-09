<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Public;

use Slink\Shared\Infrastructure\Attribute\Groups;

#[Groups(['public'])]
final readonly class PublicUserSettings {
  public function __construct(
    #[Groups(['public'])]
    public bool $allowRegistration,
  ) {}
  
  /**
   * @param array<string, mixed> $settings
   */
  public static function fromArray(array $settings): self {
    return new self(
      $settings['allowRegistration'] ?? false,
    );
  }
}
