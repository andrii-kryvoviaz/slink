<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slik\Image\Application\Query\GetImageById\GetImageByIdQuery;
use Slik\Shared\Application\Query\QueryTrait;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/image/{id}/detail', name: 'get_image_by_id', methods: ['GET'])]
final class GetImageByIdController {
  use QueryTrait;
  
  public function __invoke(
    string $id,
  ): ApiResponse {
    $image = $this->ask(new GetImageByIdQuery($id));
    
    return ApiResponse::one($image);
  }
}