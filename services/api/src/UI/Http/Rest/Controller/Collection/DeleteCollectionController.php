<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Collection;

use Slink\Collection\Application\Command\DeleteCollection\DeleteCollectionCommand;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/collection', name: 'delete_collection', methods: ['DELETE'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class DeleteCollectionController {
  use CommandTrait;

  public function __invoke(
    #[MapRequestPayload] DeleteCollectionCommand $command,
    #[CurrentUser] JWTUser $user,
  ): ApiResponse {
    $this->handleSync($command->withContext(['userId' => $user->getIdentifier()]));

    return ApiResponse::empty();
  }
}
