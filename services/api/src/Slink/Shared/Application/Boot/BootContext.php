<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot;

use Symfony\Component\Console\Application;

final readonly class BootContext {
  public function __construct(
    public Application $application,
  ) {}
}
