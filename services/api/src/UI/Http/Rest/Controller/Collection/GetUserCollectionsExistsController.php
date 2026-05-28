<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Collection;

use Slink\Collection\Application\Query\GetUserCollectionsExists\GetUserCollectionsExistsQuery;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/collections/exists', name: 'get_user_collections_exists', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class GetUserCollectionsExistsController {
  use QueryTrait;

  public function __invoke(
    #[CurrentUser] JwtUser                            $user,
    #[MapQueryString] GetUserCollectionsExistsQuery   $query = new GetUserCollectionsExistsQuery(),
  ): ApiResponse {
    $exists = (bool) $this->ask($query->withContext([
      'userId' => $user->getIdentifier(),
    ]));

    return ApiResponse::fromPayload(['exists' => $exists]);
  }
}
