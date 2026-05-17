<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Collection;

use Slink\Collection\Application\Query\GetAccessibleCollection\GetAccessibleCollectionQuery;
use Slink\Collection\Application\Query\GetCollectionItemsExists\GetCollectionItemsExistsQuery;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\Shared\Infrastructure\Security\Voter\GuestAccessVoter;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/collection/{id}/items/exists', name: 'get_collection_items_exists', methods: ['GET'])]
#[IsGranted(GuestAccessVoter::GUEST_COLLECTION_SHARE_ALLOWED)]
final readonly class GetCollectionItemsExistsController {
  use QueryTrait;

  public function __invoke(
    string $id,
    #[CurrentUser] ?JwtUser $user = null,
  ): ApiResponse {
    $accessQuery = new GetAccessibleCollectionQuery($id);
    $collection = $this->ask($accessQuery->withContext(['userId' => $user?->getIdentifier()]));

    if ($collection === null) {
      return ApiResponse::empty(Response::HTTP_NOT_FOUND);
    }

    $exists = (bool) $this->ask(new GetCollectionItemsExistsQuery($id));

    return ApiResponse::fromPayload(['exists' => $exists]);
  }
}
