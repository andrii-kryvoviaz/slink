<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Application\Query\GetPublicImageById\GetPublicImageByIdQuery;
use Slink\Image\Infrastructure\Resource\ImageResourceContext;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\Shared\Infrastructure\Security\Voter\GuestAccessVoter;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/image/{id}/public', name: 'get_public_image_by_id', methods: ['GET'])]
#[IsGranted(GuestAccessVoter::GUEST_VIEW_ALLOWED)]
final class GetPublicImageByIdController {
  use QueryTrait;

  public function __invoke(string $id, #[CurrentUser] ?JwtUser $user = null): ApiResponse {
    $query = new GetPublicImageByIdQuery($id);

    $resourceContext = new ImageResourceContext(
      viewerUserId: $user?->getIdentifier(),
      groups: ['public', 'bookmark', 'tag'],
    );

    $image = $this->ask($query->withContext([
      'resourceContext' => $resourceContext,
    ]));

    return ApiResponse::one($image);
  }
}
