<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Store;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

trait BootsIndependentKernels {
  /** @var list<KernelInterface> */
  private array $independentKernels = [];

  protected function independentKernel(): KernelInterface {
    $kernel = static::createKernel();
    $kernel->boot();
    $this->independentKernels[] = $kernel;

    return $kernel;
  }

  protected function independentContainer(): ContainerInterface {
    /** @var ContainerInterface $container */
    $container = $this->independentKernel()->getContainer()->get('test.service_container');

    return $container;
  }

  private function shutDownIndependentKernels(): void {
    foreach ($this->independentKernels as $kernel) {
      $kernel->shutdown();
    }

    $this->independentKernels = [];
  }
}
