<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Application\Query\GetImageList\GetImageListQuery;
use Slink\Shared\Application\Query\QueryTrait;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route('/images/{page}', name: 'get_image_list', requirements: ['page' => '\d+'], methods: ['GET'])]
final class GetImageListController {
  use QueryTrait;
  
  public function __invoke(
    #[MapQueryString] GetImageListQuery $query,
    int $page = 1
  ): ApiResponse {
    $images = $this->ask($query->withContext([
      'page' => $page,
      'isPublic' => true
    ]));
    
    return ApiResponse::collection($images);
  }
}