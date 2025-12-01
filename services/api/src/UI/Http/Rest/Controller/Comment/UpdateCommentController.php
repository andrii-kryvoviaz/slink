<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Comment;

use Slink\Comment\Application\Command\UpdateComment\UpdateCommentCommand;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/comment/{commentId}', name: 'update_comment', methods: ['PATCH'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class UpdateCommentController {
  use CommandTrait;

  public function __invoke(
    #[MapRequestPayload] UpdateCommentCommand $command,
    #[CurrentUser] JWTUser $user,
    string $commentId,
  ): ApiResponse {
    $this->handle($command->withContext([
      'commentId' => $commentId,
      'userId' => $user->getIdentifier(),
    ]));

    return ApiResponse::empty();
  }
}
