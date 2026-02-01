<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Bookmark;

use Slink\Bookmark\Application\Query\GetUserBookmarks\GetUserBookmarksQuery;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/bookmarks', name: 'get_user_bookmarks', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class GetUserBookmarksController {
  use QueryTrait;

  public function __invoke(
    #[MapQueryString] GetUserBookmarksQuery $query,
    #[CurrentUser] JWTUser $user,
  ): ApiResponse {
    $bookmarks = $this->ask(
      $query->withContext(['userId' => $user->getIdentifier()])
    );

    return ApiResponse::collection($bookmarks);
  }
}
