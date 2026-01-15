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
    public ?PublicShareSettings $share = null,
    
    #[Groups(['public'])]
    public ?PublicDemoSettings $demo = null,
    
    #[Groups(['public'])]
    public ?PublicImageSettings $image = null,
  ) {}
  
  /**
   * @param array<string, mixed> $settings
   */
  public static function fromArray(array $settings): self {
    return new self(
      isset($settings['user']) ? PublicUserSettings::fromArray($settings['user']) : null,
      isset($settings['access']) ? PublicAccessSettings::fromArray($settings['access']) : null,
      isset($settings['share']) ? PublicShareSettings::fromArray($settings['share']) : null,
      isset($settings['demo']) ? PublicDemoSettings::fromArray($settings['demo']) : null,
      isset($settings['image']) ? PublicImageSettings::fromArray($settings['image']) : null,
    );
  }
}
