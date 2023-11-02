<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slik\Image\Application\Command\UploadImage\UploadImageCommand;
use Slik\Shared\Application\Command\CommandTrait;
use Slik\Shared\Application\Http\RequestValueResolver\FileRequestValueResolver;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/upload', name: 'upload_image', methods: ['POST', 'PUT'])]
final class UploadImageController {
  use CommandTrait;
  
  public function __invoke(
    #[MapRequestPayload(
      resolver: FileRequestValueResolver::class
    )] UploadImageCommand $command
  ): ApiResponse {
    $this->handle($command);
    
    return ApiResponse::created($command->getId()->toString(),"image/{$command->getId()}/detail");
  }
}