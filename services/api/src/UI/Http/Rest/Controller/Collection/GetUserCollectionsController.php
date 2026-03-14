<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Collection;

use Slink\Collection\Application\Query\GetUserCollections\GetUserCollectionsQuery;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/collections', name: 'get_user_collections', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class GetUserCollectionsController {
  use QueryTrait;

  public function __invoke(
    #[MapQueryString] GetUserCollectionsQuery $query,
    #[CurrentUser] JWTUser                    $user,
  ): ApiResponse {
    $collections = $this->ask($query->withContext([
      'userId' => $user->getIdentifier(),
    ]));

    return ApiResponse::collection($collections);
  }
}
