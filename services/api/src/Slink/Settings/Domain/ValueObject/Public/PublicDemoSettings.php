<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Public;

use Slink\Shared\Infrastructure\Attribute\Groups;

#[Groups(['public'])]
final readonly class PublicDemoSettings {
  public function __construct(
    #[Groups(['public'])]
    public bool $enabled = false,
    
    #[Groups(['public'])]
    public string $demoUsername = 'demo',
    
    #[Groups(['public'])]
    public string $demoPassword = 'demo',
    
    #[Groups(['public'])]
    public int $resetIntervalMinutes = 120,
  ) {}
  
  /**
   * @param array<string, mixed> $settings
   */
  public static function fromArray(array $settings): self {
    return new self(
      enabled: $settings['enabled'] ?? false,
      demoUsername: $settings['demoUsername'] ?? 'demo',
      demoPassword: $settings['demoPassword'] ?? 'demo',
      resetIntervalMinutes: $settings['resetIntervalMinutes'] ?? 120
    );
  }
}
