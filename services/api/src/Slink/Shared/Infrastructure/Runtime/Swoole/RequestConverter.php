<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Runtime\Swoole;

use Swoole\Http\Request;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

final class RequestConverter {
    public function convert(Request $swooleRequest): SymfonyRequest {
        $request = new SymfonyRequest(
            $swooleRequest->get ?? [],
            $swooleRequest->post ?? [],
            [],
            $swooleRequest->cookie ?? [],
            $swooleRequest->files ?? [],
            array_change_key_case($swooleRequest->server ?? [], CASE_UPPER),
            $swooleRequest->rawContent()
        );
        $request->headers = new HeaderBag($swooleRequest->header ?? []);

        return $request;
    }
}
