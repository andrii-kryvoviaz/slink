<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Tag;

use Slink\Shared\Application\Query\QueryTrait;
use Slink\Tag\Application\Query\GetImageTags\GetImageTagsQuery;
use Slink\User\Domain\Contracts\UserInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/images/{imageId}/tags', name: 'get_image_tags', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class GetImageTagsController {
  use QueryTrait;

  public function __invoke(
    string $imageId,
    #[CurrentUser] UserInterface $user
  ): ApiResponse {
    $query = new GetImageTagsQuery($imageId);
    
    $tags = $this->ask($query->withContext([
      'userId' => $user->getIdentifier()
    ]));

    return ApiResponse::collection($tags);
  }
}