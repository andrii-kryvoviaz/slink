<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Application\Query\GetImageListExists\GetImageListExistsQuery;
use Slink\Image\Application\Service\ImageVisibilityResolver;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\Shared\Infrastructure\Security\Voter\GuestAccessVoter;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route('/images/exists', name: 'get_image_list_exists', methods: ['GET'])]
#[IsGranted(GuestAccessVoter::GUEST_VIEW_ALLOWED)]
final class GetImageListExistsController {
  use QueryTrait;

  public function __construct(
    private readonly ImageVisibilityResolver $visibilityResolver,
  ) {
  }

  public function __invoke(
    #[MapQueryString] GetImageListExistsQuery $query = new GetImageListExistsQuery(),
  ): ApiResponse {
    $exists = (bool) $this->ask($query->withContext([
      'isPublic' => $this->visibilityResolver->resolveListIsPublicFilter(),
    ]));

    return ApiResponse::fromPayload(['exists' => $exists]);
  }
}
