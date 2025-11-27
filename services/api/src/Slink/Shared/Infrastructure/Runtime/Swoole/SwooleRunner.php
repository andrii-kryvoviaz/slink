<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Runtime\Swoole;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\Runtime\RunnerInterface;

final class SwooleRunner implements RunnerInterface {
    public function __construct(
        private readonly SwooleServerFactory $serverFactory,
        private readonly HttpKernelInterface $application,
        private readonly RequestConverter $requestConverter = new RequestConverter(),
        private readonly ResponseEmitter $responseEmitter = new ResponseEmitter()
    ) {}

    public function run(): int {
        $this->serverFactory->create($this->handle(...))->start();

        return 0;
    }

    private function handle(Request $request, Response $response): void {
        $symfonyRequest = $this->requestConverter->convert($request);
        $symfonyResponse = $this->application->handle($symfonyRequest);

        $this->responseEmitter->emit($symfonyResponse, $response);

        if ($this->application instanceof TerminableInterface) {
            $this->application->terminate($symfonyRequest, $symfonyResponse);
        }
    }
}
