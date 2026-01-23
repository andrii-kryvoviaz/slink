<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Collection;

use Slink\Collection\Application\Command\RemoveItemFromCollection\RemoveItemFromCollectionCommand;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/collection/{id}/items/{itemId}', name: 'remove_item_from_collection', methods: ['DELETE'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class RemoveItemFromCollectionController {
  use CommandTrait;

  public function __invoke(
    #[CurrentUser] JWTUser $user,
    string $id,
    string $itemId,
  ): ApiResponse {
    $command = new RemoveItemFromCollectionCommand($id, $itemId);
    $this->handleSync($command->withContext(['userId' => $user->getIdentifier()]));

    return ApiResponse::empty();
  }
}
