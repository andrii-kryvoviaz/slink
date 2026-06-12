<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot\Step;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Application\Boot\BootCategory;
use Slink\Shared\Application\Boot\BootContext;
use Slink\Shared\Application\Boot\BootResult;
use Slink\Shared\Application\Boot\BootStepInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 90)]
final readonly class StorageInfoStep implements BootStepInterface {
  public function __construct(
    private ConfigurationProviderInterface $config,
  ) {}

  public function label(): string {
    return 'storage';
  }

  public function category(): BootCategory {
    return BootCategory::Config;
  }

  public function run(BootContext $context): BootResult {
    return BootResult::settings([
      ['Storage Provider', (string) $this->config->get('storage.provider')],
      ['Maximum Upload File Size', (string) $this->config->get('image.maxSize')],
      ['Upload Chunk Size', (string) $this->config->get('image.chunkSize')],
    ]);
  }
}
