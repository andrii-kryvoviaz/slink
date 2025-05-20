<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\DependencyInjection\Compiler;

use EventSauce\EventSourcing\MessageDispatcher;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class EventSauceDispatcherCompilerPass implements CompilerPassInterface {

  /**
   * @inheritDoc
   */
  public function process(ContainerBuilder $container): void {
    if (!$container->has(MessageDispatcher::class)) {
      return;
    }

    $definition = $container->findDefinition(MessageDispatcher::class);
    $taggedServices = $container->findTaggedServiceIds('event_sauce.event_consumer');

    foreach ($taggedServices as $id => $tags) {
      $definition->addMethodCall('addConsumer', [new Reference($id)]);
    }
  }
}