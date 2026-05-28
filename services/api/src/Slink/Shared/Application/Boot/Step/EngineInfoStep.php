<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot\Step;

use Slink\Shared\Application\Boot\BootCategory;
use Slink\Shared\Application\Boot\BootContext;
use Slink\Shared\Application\Boot\BootResult;
use Slink\Shared\Application\Boot\BootStepInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsTaggedItem(priority: 100)]
final readonly class EngineInfoStep implements BootStepInterface {
  public function __construct(
    #[Autowire(param: 'slink.runtime_dir')]
    private string $runtimeDir,
  ) {}

  public function label(): string {
    return 'engine';
  }

  public function category(): BootCategory {
    return BootCategory::Info;
  }

  public function run(BootContext $context): BootResult {
    $rows = [];

    $container = $this->readRuntimeFile('container');
    if ($container !== null) {
      $rows[] = ['Container', $container];
    }

    $rows[] = ['PHP', PHP_VERSION];

    $node = $this->readRuntimeFile('node');
    if ($node !== null) {
      $rows[] = ['Node', $node];
    }

    return BootResult::settings($rows);
  }

  private function readRuntimeFile(string $name): ?string {
    $path = $this->runtimeDir . '/' . $name;
    $contents = @file_get_contents($path);

    if (!is_string($contents)) {
      return null;
    }

    $value = trim($contents);

    return $value === '' ? null : $value;
  }
}
