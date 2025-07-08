<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\External;

use Slink\Image\Application\Command\UploadImage\UploadImageCommand;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Application\Http\RequestValueResolver\FileRequestValueResolver;
use Slink\User\Infrastructure\Auth\ApiKeyUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: 'external/upload', name: 'external_upload', methods: ['POST', 'PUT'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class UploadController {
  use CommandTrait;
  
  public function __invoke(
    #[MapRequestPayload(
      resolver: FileRequestValueResolver::class
    )] UploadImageCommand $command,
    #[CurrentUser] ApiKeyUser $user
  ): ApiResponse {
    $this->handle($command->withContext([
      'userId' => $user->getIdentifier()
    ]));
    
    return ApiResponse::created($command->getId()->toString(),"image/{$command->getId()}/detail");
  }
}
