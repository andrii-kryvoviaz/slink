<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Share;

use Slink\Share\Application\Query\ListShares\ListSharesQuery;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/shares', name: 'list_shares', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class ListSharesController {
  use QueryTrait;

  public function __invoke(
    #[MapQueryString] ?ListSharesQuery $query = null,
    #[CurrentUser] ?JwtUser $user = null,
  ): ApiResponse {
    $query ??= new ListSharesQuery();

    $collection = $this->ask($query->withContext([
      'userId' => $user?->getIdentifier(),
    ]));

    return ApiResponse::collection($collection);
  }
}
