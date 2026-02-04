<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Application\Command\BatchDeleteImages\BatchDeleteImagesCommand;
use Slink\Image\Application\Command\BatchDeleteImages\BatchDeleteImagesResult;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/images/batch-delete', name: 'batch_delete_images', methods: ['DELETE'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class BatchDeleteImagesController {
  use CommandTrait;

  public function __invoke(
    #[MapRequestPayload] BatchDeleteImagesCommand $command,
    #[CurrentUser] JWTUser $user,
  ): ApiResponse {
    /** @var BatchDeleteImagesResult $result */
    $result = $this->handleSync($command->withContext([
      'userId' => $user->getIdentifier(),
    ]));

    return ApiResponse::fromPayload($result->toArray());
  }
}
