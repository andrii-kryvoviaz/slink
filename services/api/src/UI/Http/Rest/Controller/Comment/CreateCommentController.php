<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Comment;

use Slink\Comment\Application\Command\CreateComment\CreateCommentCommand;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/image/{imageId}/comments', name: 'create_comment', methods: ['POST'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class CreateCommentController {
  use CommandTrait;

  public function __invoke(
    #[MapRequestPayload] CreateCommentCommand $command,
    #[CurrentUser] JWTUser $user,
    string $imageId,
  ): ApiResponse {
    $this->handle($command->withContext([
      'imageId' => $imageId,
      'userId' => $user->getIdentifier(),
    ]));

    return ApiResponse::created();
  }
}
