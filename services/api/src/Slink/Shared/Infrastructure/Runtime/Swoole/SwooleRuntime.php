<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Runtime\Swoole;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Runtime\RunnerInterface;
use Symfony\Component\Runtime\SymfonyRuntime;

final class SwooleRuntime extends SymfonyRuntime {
    private readonly SwooleServerFactory $serverFactory;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options = []) {
        $this->serverFactory = new SwooleServerFactory($options);
        parent::__construct($options);
    }

    public function getRunner(?object $application): RunnerInterface {
        if ($application instanceof HttpKernelInterface) {
            return new SwooleRunner($this->serverFactory, $application);
        }

        return parent::getRunner($application);
    }
}
