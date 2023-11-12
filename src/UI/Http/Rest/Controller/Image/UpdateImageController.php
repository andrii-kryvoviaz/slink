<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slik\Image\Application\Command\UpdateImage\UpdateImageCommand;
use Slik\Shared\Application\Command\CommandTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/image/{id}', name: 'update_image', methods: ['PATCH'])]
final readonly class UpdateImageController {
  use CommandTrait;
  
  public function __invoke(string $id, #[MapRequestPayload] UpdateImageCommand $command): ApiResponse {
    $this->handle($command->withId($id));
    
    return ApiResponse::empty(Response::HTTP_NO_CONTENT);
  }
}