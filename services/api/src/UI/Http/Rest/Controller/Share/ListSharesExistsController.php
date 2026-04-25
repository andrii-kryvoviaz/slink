<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Share;

use Slink\Share\Application\Query\ListSharesExists\ListSharesExistsQuery;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/shares/exists', name: 'list_shares_exists', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class ListSharesExistsController {
  use QueryTrait;

  public function __invoke(
    #[MapQueryString] ListSharesExistsQuery $query = new ListSharesExistsQuery(),
    #[CurrentUser] ?JwtUser $user = null,
  ): ApiResponse {
    $exists = (bool) $this->ask($query->withContext([
      'userId' => $user?->getIdentifier(),
    ]));

    return ApiResponse::fromPayload(['exists' => $exists]);
  }
}
