<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Tag;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\Tag\Application\Command\UntagImage\UntagImageCommand;
use Slink\User\Domain\Contracts\UserInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/images/{imageId}/tags/{tagId}', name: 'untag_image', methods: ['DELETE'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class UntagImageController {
  use CommandTrait;

  public function __invoke(
    string $imageId,
    string $tagId,
    #[CurrentUser] UserInterface $user
  ): ApiResponse {
    $command = new UntagImageCommand($imageId, $tagId);
    
    $this->handle($command->withContext([
      'userId' => $user->getIdentifier()
    ]));

    return ApiResponse::empty();
  }
}