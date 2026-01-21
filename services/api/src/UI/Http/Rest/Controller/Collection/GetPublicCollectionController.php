<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Collection;

use Slink\Collection\Application\Query\GetAccessibleCollection\GetAccessibleCollectionQuery;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/collection/{id}', name: 'get_shared_collection', methods: ['GET'], priority: -1)]
final readonly class GetPublicCollectionController {
  use QueryTrait;

  public function __invoke(
    string $id,
    #[CurrentUser] ?JwtUser $user = null,
  ): ApiResponse {
    $query = new GetAccessibleCollectionQuery($id);
    $collection = $this->ask($query->withContext(['userId' => $user?->getIdentifier()]));

    if ($collection === null) {
      return ApiResponse::empty(Response::HTTP_NOT_FOUND);
    }

    return ApiResponse::one($collection);
  }
}
