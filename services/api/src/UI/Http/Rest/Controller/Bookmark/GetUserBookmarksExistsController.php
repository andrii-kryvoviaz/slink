<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Bookmark;

use Slink\Bookmark\Application\Query\GetUserBookmarksExists\GetUserBookmarksExistsQuery;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/bookmarks/exists', name: 'get_user_bookmarks_exists', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class GetUserBookmarksExistsController {
  use QueryTrait;

  public function __invoke(
    #[CurrentUser] JwtUser $user,
  ): ApiResponse {
    $query = new GetUserBookmarksExistsQuery();
    $exists = (bool) $this->ask($query->withContext([
      'userId' => $user->getIdentifier(),
    ]));

    return ApiResponse::fromPayload(['exists' => $exists]);
  }
}
