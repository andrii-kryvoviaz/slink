<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Application\Command\UpdateImage\UpdateImageCommand;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/image/{id}', name: 'update_image', methods: ['PATCH'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class UpdateImageController {
  use CommandTrait;
  
  public function __invoke(
    #[MapRequestPayload] UpdateImageCommand $command,
    #[CurrentUser] JWTUser $user,
    string $id
  ): ApiResponse {
    $this->handle($command->withContext([
      'id' => $id,
      'user' => $user,
    ]));
    
    return ApiResponse::empty();
  }
}