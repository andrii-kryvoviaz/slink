<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Collection;

use Slink\Collection\Application\Query\GetAccessibleCollection\GetAccessibleCollectionQuery;
use Slink\Collection\Application\Query\GetCollectionItems\GetCollectionItemsQuery;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/collection/{id}/items', name: 'get_shared_collection_items', methods: ['GET'], priority: -1)]
final readonly class GetPublicCollectionItemsController {
  use QueryTrait;

  public function __invoke(
    string $id,
    #[MapQueryString] GetCollectionItemsQuery $query,
    #[CurrentUser] ?JwtUser $user = null,
  ): ApiResponse {
    $accessQuery = new GetAccessibleCollectionQuery($id);
    $collection = $this->ask($accessQuery->withContext(['userId' => $user?->getIdentifier()]));

    if ($collection === null) {
      return ApiResponse::empty(Response::HTTP_NOT_FOUND);
    }

    $result = $this->ask($query->withCollectionId($id));

    return ApiResponse::collection($result);
  }
}
