<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Core;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/health', name: 'health_check', methods: ['GET'])]
final readonly class HealthCheckController {
  public function __invoke(): Response {
    return new Response('OK', Response::HTTP_OK);
  }
}