<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slik\Image\Application\Command\UploadImageCommand;
use Slik\Shared\Application\Command\CommandTrait;
use Slik\Shared\Application\Http\RequestValueResolver\FileRequestValueResolver;
use Slik\Shared\Application\Query\QueryTrait;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/upload', name: 'upload_image', methods: ['POST', 'PUT'])]
final class UploadImageController {
  use CommandTrait;
  use QueryTrait;
  
  public function __invoke(
    #[MapRequestPayload(
      resolver: FileRequestValueResolver::class
    )] UploadImageCommand $command
  ): ApiResponse {
    $this->handle($command);
    
    return ApiResponse::created('/upload');
  }
}