<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Bookmark;

use Slink\Bookmark\Application\Query\GetBookmarkStatus\GetBookmarkStatusQuery;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/image/{imageId}/bookmark/status', name: 'get_bookmark_status', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class GetBookmarkStatusController {
  use QueryTrait;

  public function __invoke(
    #[CurrentUser] JWTUser $user,
    string $imageId,
  ): ApiResponse {
    $query = new GetBookmarkStatusQuery($imageId);
    $status = $this->ask($query->withContext(['userId' => $user->getIdentifier()]));

    return ApiResponse::fromPayload($status);
  }
}
