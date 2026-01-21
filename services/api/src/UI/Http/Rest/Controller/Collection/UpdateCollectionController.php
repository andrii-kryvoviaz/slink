<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Collection;

use Slink\Collection\Application\Command\UpdateCollection\UpdateCollectionCommand;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/collection', name: 'update_collection', methods: ['PATCH'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class UpdateCollectionController {
  use CommandTrait;

  public function __invoke(
    #[CurrentUser] JWTUser $user,
    #[MapRequestPayload] UpdateCollectionCommand $command,
  ): ApiResponse {
    $this->handleSync($command->withContext(['userId' => $user->getIdentifier()]));

    return ApiResponse::empty();
  }
}
