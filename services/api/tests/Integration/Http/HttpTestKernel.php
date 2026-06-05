<?php

declare(strict_types=1);

namespace Tests\Integration\Http;

use Slink\Shared\Infrastructure\Kernel;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tests\Integration\Http\Double\NullServerSentEventPublisher;

final class HttpTestKernel extends Kernel implements CompilerPassInterface {
  #[\Override]
  public function getProjectDir(): string {
    return \dirname(__DIR__, 3);
  }

  #[\Override]
  protected function build(ContainerBuilder $container): void {
    parent::build($container);

    $container->addCompilerPass($this, PassConfig::TYPE_BEFORE_REMOVING, -1000);
  }

  #[\Override]
  public function process(ContainerBuilder $container): void {
    $publisherId = \Slink\Shared\Infrastructure\Service\MercureServerSentEventPublisher::class;

    if ($container->hasDefinition($publisherId)) {
      $definition = $container->getDefinition($publisherId);
      $definition->setClass(NullServerSentEventPublisher::class);
      $definition->setArguments([]);
    }

    if ($container->hasParameter('storage')) {
      /** @var array<string, mixed> $storage */
      $storage = $container->getParameter('storage');
      $storageDir = $this->getProjectDir() . '/var/functional-test/storage';

      if (!\is_dir($storageDir)) {
        \mkdir($storageDir, 0777, true);
      }

      if (isset($storage['adapter']['local']) && \is_array($storage['adapter']['local'])) {
        $storage['adapter']['local']['dir'] = $storageDir;
      }

      $container->setParameter('storage', $storage);
    }
  }
}
