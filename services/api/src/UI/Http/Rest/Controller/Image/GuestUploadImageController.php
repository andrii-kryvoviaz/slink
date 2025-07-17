<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Application\Command\UploadImage\UploadImageCommand;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Application\Http\RequestValueResolver\FileRequestValueResolver;
use Slink\Shared\Infrastructure\Security\Voter\GuestAccessVoter;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/guest/upload', name: 'guest_upload_image', methods: ['POST', 'PUT'])]
#[IsGranted(GuestAccessVoter::GUEST_UPLOAD_ALLOWED)]
final readonly class GuestUploadImageController {
  use CommandTrait;
  
  public function __invoke(
    #[MapRequestPayload(
      resolver: FileRequestValueResolver::class
    )] UploadImageCommand $command
  ): ApiResponse {
    $this->handle($command);
    
    return ApiResponse::created($command->getId()->toString());
  }
}
