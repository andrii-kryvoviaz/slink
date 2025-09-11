<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Public;

use Slink\Shared\Infrastructure\Attribute\Groups;

#[Groups(['public'])]
final readonly class PublicApplicationSettings {
  public function __construct(
    #[Groups(['public'])]
    public ?PublicUserSettings $user = null,
    
    #[Groups(['public'])]
    public ?PublicAccessSettings $access = null,
    
    #[Groups(['public'])]
    public ?PublicDemoSettings $demo = null,
  ) {}
  
  /**
   * @param array<string, mixed> $settings
   */
  public static function fromArray(array $settings): self {
    return new self(
      isset($settings['user']) ? PublicUserSettings::fromArray($settings['user']) : null,
      isset($settings['access']) ? PublicAccessSettings::fromArray($settings['access']) : null,
      isset($settings['demo']) ? PublicDemoSettings::fromArray($settings['demo']) : null
    );
  }
}
