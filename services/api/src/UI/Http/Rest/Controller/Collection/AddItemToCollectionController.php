<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Collection;

use Slink\Collection\Application\Command\AddItemToCollection\AddItemToCollectionCommand;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/collection/{id}/items/{itemId}', name: 'add_item_to_collection', methods: ['POST'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class AddItemToCollectionController {
  use CommandTrait;

  public function __invoke(
    #[CurrentUser] JWTUser $user,
    string $id,
    string $itemId,
  ): ApiResponse {
    $command = new AddItemToCollectionCommand($id, $itemId);
    $this->handleSync($command->withContext(['userId' => $user->getIdentifier()]));

    return ApiResponse::empty(ApiResponse::HTTP_CREATED);
  }
}
