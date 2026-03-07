<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Application\Command\BatchImages\BatchImagesCommand;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/images/batch', name: 'batch_images', methods: ['PATCH'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class BatchImagesController {
  use CommandTrait;

  public function __invoke(
    #[MapRequestPayload] BatchImagesCommand $command,
    #[CurrentUser] JwtUser $user,
  ): ApiResponse {
    $result = $this->handleSync($command->withContext([
      'userId' => $user->getIdentifier(),
    ]));

    return ApiResponse::fromPayload($result);
  }
}
