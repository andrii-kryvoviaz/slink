<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Collection;

use Slink\Collection\Application\Query\GetUserCollections\GetUserCollectionsQuery;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/collections/{page}', name: 'get_user_collections', requirements: ['page' => '\d+'], methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class GetUserCollectionsController {
  use QueryTrait;

  public function __invoke(
    #[CurrentUser] JWTUser $user,
    int $page = 1,
  ): ApiResponse {
    $query = new GetUserCollectionsQuery($user->getIdentifier(), $page);
    $collections = $this->ask($query);

    return ApiResponse::collection($collections);
  }
}
