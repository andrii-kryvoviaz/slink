<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Comment;

use Slink\Comment\Application\Command\CreateComment\CreateCommentCommand;
use Slink\Comment\Domain\Repository\CommentRepositoryInterface;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Application\Http\Item;
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

  public function __construct(
    private CommentRepositoryInterface $commentRepository,
  ) {
  }

  public function __invoke(
    #[MapRequestPayload] CreateCommentCommand $command,
    #[CurrentUser] JWTUser $user,
    string $imageId,
  ): ApiResponse {
    $commentId = $this->handleSync($command->withContext([
      'imageId' => $imageId,
      'userId' => $user->getIdentifier(),
    ]));

    $comment = $this->commentRepository->oneById($commentId->toString());

    return ApiResponse::one(Item::fromEntity($comment), ApiResponse::HTTP_CREATED);
  }
}
