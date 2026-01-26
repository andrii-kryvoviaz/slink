<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Application\Query\GetImageList\GetImageListQuery;
use Slink\Image\Infrastructure\Resource\ImageResourceContext;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route('/images/history/{page}', name: 'get_image_history', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class GetImageHistoryController {
  use QueryTrait;

  public function __invoke(
    #[MapQueryString] GetImageListQuery $query,
    #[CurrentUser] JwtUser              $user,
    int                                 $page = 1
  ): ApiResponse {
    $resourceContext = new ImageResourceContext(
      groups: ['public', 'private', 'tag', 'collection'],
      viewerUserId: $user->getIdentifier(),
    );

    $collection = $this->ask($query->withContext([
      'page' => $page,
      'userId' => $user->getIdentifier(),
      'resourceContext' => $resourceContext,
    ]));

    return ApiResponse::collection($collection);
  }
}