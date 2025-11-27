<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Runtime\Swoole;

use Swoole\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ResponseEmitter {
    public function emit(SymfonyResponse $symfonyResponse, Response $swooleResponse): void {
        $this->emitHeaders($symfonyResponse, $swooleResponse);
        $swooleResponse->status($symfonyResponse->getStatusCode());
        $this->emitBody($symfonyResponse, $swooleResponse);
    }

    private function emitHeaders(SymfonyResponse $symfonyResponse, Response $swooleResponse): void {
        foreach ($symfonyResponse->headers->all() as $name => $values) {
            foreach ((array) $values as $value) {
                $swooleResponse->header((string) $name, $value);
            }
        }
    }

    private function emitBody(SymfonyResponse $symfonyResponse, Response $swooleResponse): void {
        match (true) {
            $this->isPartialBinaryResponse($symfonyResponse),
            $symfonyResponse instanceof StreamedResponse => $this->emitStreamedBody($symfonyResponse, $swooleResponse),
            $symfonyResponse instanceof BinaryFileResponse => $swooleResponse->sendfile($symfonyResponse->getFile()->getPathname()),
            default => $swooleResponse->end($symfonyResponse->getContent())
        };
    }

    private function isPartialBinaryResponse(SymfonyResponse $response): bool {
        return $response instanceof BinaryFileResponse && $response->headers->has('Content-Range');
    }

    private function emitStreamedBody(SymfonyResponse $symfonyResponse, Response $swooleResponse): void {
        ob_start(function (string $buffer) use ($swooleResponse): string {
            $swooleResponse->write($buffer);
            return '';
        }, 4096);

        $symfonyResponse->sendContent();
        ob_end_clean();
        $swooleResponse->end();
    }
}
