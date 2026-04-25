<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Application\Query\GetImageHistoryExists\GetImageHistoryExistsQuery;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route('/images/history/exists', name: 'get_image_history_exists', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class GetImageHistoryExistsController {
  use QueryTrait;

  public function __invoke(
    #[CurrentUser] JwtUser                       $user,
    #[MapQueryString] GetImageHistoryExistsQuery $query = new GetImageHistoryExistsQuery(),
  ): ApiResponse {
    $exists = (bool) $this->ask($query->withContext([
      'userId' => $user->getIdentifier(),
    ]));

    return ApiResponse::fromPayload(['exists' => $exists]);
  }
}
