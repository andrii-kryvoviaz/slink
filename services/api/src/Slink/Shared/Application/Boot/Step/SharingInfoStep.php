<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot\Step;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Application\Boot\BootCategory;
use Slink\Shared\Application\Boot\BootContext;
use Slink\Shared\Application\Boot\BootResult;
use Slink\Shared\Application\Boot\BootStepInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 60)]
final readonly class SharingInfoStep implements BootStepInterface {
  public function __construct(
    private ConfigurationProviderInterface $config,
  ) {}

  public function label(): string {
    return 'sharing';
  }

  public function category(): BootCategory {
    return BootCategory::Config;
  }

  public function run(BootContext $context): BootResult {
    return BootResult::settings([
      ['Short URL Generation', $this->boolLabel('share.enableUrlShortening')],
    ]);
  }

  private function boolLabel(string $key): string {
    return ((bool) $this->config->get($key)) ? 'Enabled' : 'Disabled';
  }
}
