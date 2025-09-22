<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Tag;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\Tag\Application\Command\DeleteTag\DeleteTagCommand;
use Slink\User\Domain\Contracts\UserInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/tags/{id}', name: 'delete_tag', methods: ['DELETE'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class DeleteTagController {
  use CommandTrait;

  public function __invoke(
    string $id,
    #[CurrentUser] UserInterface $user
  ): ApiResponse {
    $command = new DeleteTagCommand($id);
    
    $this->handle($command->withContext([
      'userId' => $user->getIdentifier()
    ]));

    return ApiResponse::empty();
  }
}