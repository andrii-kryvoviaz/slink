<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Application\Query\GetOAuthProviders\GetOAuthProvidersQuery;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/auth/sso/providers', name: 'auth_sso_providers', methods: ['GET'])]
final class GetOAuthProvidersController {
  use QueryTrait;

  public function __invoke(): ApiResponse {
    $providers = $this->ask(
      (new GetOAuthProvidersQuery())->withContext(['groups' => ['public']]),
    );

    return ApiResponse::list($providers);
  }
}
