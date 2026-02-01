<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Application\Query\GetImageListById\GetImageListByIdQuery;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route('/images/by-id', name: 'get_image_list_by_id', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class GetImageListByIdController {
  use QueryTrait;
  
  public function __invoke(
    #[MapQueryString] GetImageListByIdQuery $query,
    #[CurrentUser] JwtUser $user,
  ): ApiResponse {
    $images = $this->ask($query->withContext([
      'userId' => $user->getIdentifier(),
    ]));
    
    return ApiResponse::list($images);
  }
}