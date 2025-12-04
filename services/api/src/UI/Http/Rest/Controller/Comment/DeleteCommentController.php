<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Comment;

use Slink\Comment\Application\Command\DeleteComment\DeleteCommentCommand;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/comment/{commentId}', name: 'delete_comment', methods: ['DELETE'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class DeleteCommentController {
  use CommandTrait;

  public function __invoke(
    #[CurrentUser] JWTUser $user,
    string $commentId,
  ): ApiResponse {
    $command = new DeleteCommentCommand();
    $this->handle($command->withContext([
      'commentId' => $commentId,
      'userId' => $user->getIdentifier(),
    ]));

    return ApiResponse::empty();
  }
}
