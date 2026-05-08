<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot\Step;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Application\Boot\BootCategory;
use Slink\Shared\Application\Boot\BootContext;
use Slink\Shared\Application\Boot\BootResult;
use Slink\Shared\Application\Boot\BootStepInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsTaggedItem(priority: 100)]
final readonly class RuntimeInfoStep implements BootStepInterface {
  public function __construct(
    private ConfigurationProviderInterface $config,
    private KernelInterface $kernel,
  ) {}

  public function label(): string {
    return 'runtime';
  }

  public function category(): BootCategory {
    return BootCategory::Config;
  }

  public function run(BootContext $context): BootResult {
    $demo = (bool) $this->config->get('demo.enabled');

    return BootResult::settings([
      ['Application Environment', $this->kernel->getEnvironment()],
      ['System Timezone', date_default_timezone_get()],
      ['Demo Mode', $demo ? 'Enabled' : 'Disabled'],
    ]);
  }
}
