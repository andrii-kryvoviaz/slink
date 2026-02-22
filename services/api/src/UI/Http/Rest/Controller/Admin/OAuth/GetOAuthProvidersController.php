<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Admin\OAuth;

use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Application\Query\GetOAuthProviders\GetOAuthProvidersQuery;
use Slink\User\Domain\Enum\UserRole;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/admin/oauth/providers', name: 'admin_get_oauth_providers', methods: ['GET'])]
#[IsGranted(UserRole::Admin->value)]
final class GetOAuthProvidersController {
  use QueryTrait;

  public function __invoke(): ApiResponse {
    $providers = $this->ask(
      (new GetOAuthProvidersQuery(enabledOnly: false))->withContext(['groups' => ['public', 'admin']]),
    );

    return ApiResponse::list($providers);
  }
}
