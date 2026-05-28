<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Application\Query\GetImageList\GetImageListQuery;
use Slink\Image\Application\Service\ImageVisibilityResolver;
use Slink\Image\Infrastructure\Resource\ImageResourceContext;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\Shared\Infrastructure\Security\Voter\GuestAccessVoter;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route('/images', name: 'get_image_list', methods: ['GET'])]
#[IsGranted(GuestAccessVoter::GUEST_VIEW_ALLOWED)]
final class GetImageListController {
  use QueryTrait;

  public function __construct(
    private readonly ImageVisibilityResolver $visibilityResolver,
  ) {
  }

  public function __invoke(
    #[MapQueryString] GetImageListQuery $query,
    #[CurrentUser] ?JWTUser             $user = null,
  ): ApiResponse {
    $resourceContext = new ImageResourceContext(
      groups: ['public', 'bookmark', 'license'],
      viewerUserId: $user?->getIdentifier(),
    );

    $collection = $this->ask($query->withContext([
      'isPublic' => $this->visibilityResolver->resolveListIsPublicFilter(),
      'resourceContext' => $resourceContext,
    ]));

    return ApiResponse::collection($collection);
  }
}