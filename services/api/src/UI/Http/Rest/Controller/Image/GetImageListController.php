<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Application\Query\GetImageList\GetImageListQuery;
use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
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
#[Route('/images/{page}', name: 'get_image_list', requirements: ['page' => '\d+'], methods: ['GET'])]
#[IsGranted(GuestAccessVoter::GUEST_VIEW_ALLOWED)]
final class GetImageListController {
  use QueryTrait;

  /**
   * @param ConfigurationProviderInterface<SettingsService> $configurationProvider
   */
  public function __construct(
    private readonly ConfigurationProviderInterface $configurationProvider
  ) {
  }

  public function __invoke(
    #[MapQueryString] GetImageListQuery $query,
    #[CurrentUser] ?JWTUser             $user = null,
    int                                 $page = 1
  ): ApiResponse {
    $isPublicFilter = $this->configurationProvider->get('image.allowOnlyPublicImages') ? null : true;

    $collection = $this->ask($query->withContext([
      'page' => $page,
      'isPublic' => $isPublicFilter,
      'viewerUserId' => $user?->getIdentifier(),
      'groups' => ['public', 'bookmark'],
    ]));

    return ApiResponse::collection($collection);
  }
}