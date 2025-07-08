<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Application\Query\GetShareXConfig\GetShareXConfigQuery;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/user/sharex-config', name: 'get_sharex_config', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class GetShareXConfigController {
  use QueryTrait;

  public function __invoke(
    #[MapQueryString] GetShareXConfigQuery $query,
    #[CurrentUser] JwtUser $user
  ): ApiResponse {
    $config = $this->ask($query->withContext([
      'userId' => $user->getIdentifier(),
    ]));

    return ApiResponse::fromPayload($config);
  }
}
