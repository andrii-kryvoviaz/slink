<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Tag;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\Tag\Application\Command\CreateTag\CreateTagCommand;
use Slink\User\Domain\Contracts\UserInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/tags', name: 'create_tag', methods: ['POST'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class CreateTagController {
  use CommandTrait;

  public function __invoke(
    #[MapRequestPayload] CreateTagCommand $command,
    #[CurrentUser] UserInterface $user
  ): ApiResponse {
    $tagId = $this->handleSync($command->withContext([
      'userId' => $user->getIdentifier()
    ]));

    return ApiResponse::created($tagId->toString(), "tags/{$tagId->toString()}");
  }
}