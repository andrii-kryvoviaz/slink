<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Tag;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\Tag\Application\Command\MoveTag\MoveTagCommand;
use Slink\User\Domain\Contracts\UserInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/tags/move', name: 'move_tag', methods: ['PATCH'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class MoveTagController {
  use CommandTrait;

  public function __invoke(
    #[MapRequestPayload] MoveTagCommand $command,
    #[CurrentUser] UserInterface $user
  ): ApiResponse {
    $this->handle($command->withContext([
      'userId' => $user->getIdentifier()
    ]));

    return ApiResponse::empty();
  }
}
