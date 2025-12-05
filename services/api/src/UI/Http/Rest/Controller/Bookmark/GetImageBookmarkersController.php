<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Bookmark;

use Slink\Bookmark\Application\Query\GetImageBookmarkers\GetImageBookmarkersQuery;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/image/{imageId}/bookmarkers/{page}', name: 'get_image_bookmarkers', requirements: ['page' => '\d+'], methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class GetImageBookmarkersController {
  use QueryTrait;

  public function __invoke(
    #[MapQueryString] GetImageBookmarkersQuery $query,
    #[CurrentUser] JWTUser $user,
    string $imageId,
    int $page = 1,
  ): ApiResponse {
    $bookmarkers = $this->ask(
      (new GetImageBookmarkersQuery($page, $imageId, $query->getLimit(), $query->getCursor()))
        ->withContext(['userId' => $user->getIdentifier()])
    );

    return ApiResponse::collection($bookmarkers);
  }
}
