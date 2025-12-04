<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Bookmark;

use Slink\Bookmark\Application\Command\RemoveBookmark\RemoveBookmarkCommand;
use Slink\Bookmark\Application\Query\GetBookmarkStatus\GetBookmarkStatusQuery;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/image/{imageId}/bookmark', name: 'remove_bookmark', methods: ['DELETE'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class RemoveBookmarkController {
  use CommandTrait;
  use QueryTrait;

  public function __invoke(
    #[CurrentUser] JWTUser $user,
    string $imageId,
  ): ApiResponse {
    $command = new RemoveBookmarkCommand($imageId);
    $this->handleSync($command->withContext(['userId' => $user->getIdentifier()]));

    $query = new GetBookmarkStatusQuery($imageId);
    $status = $this->ask($query->withContext(['userId' => $user->getIdentifier()]));

    return ApiResponse::fromPayload($status);
  }
}
