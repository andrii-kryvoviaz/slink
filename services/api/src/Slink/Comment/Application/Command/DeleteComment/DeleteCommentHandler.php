<?php

declare(strict_types=1);

namespace Slink\Comment\Application\Command\DeleteComment;

use Slink\Comment\Domain\Repository\CommentRepositoryInterface;
use Slink\Comment\Domain\Repository\CommentStoreRepositoryInterface;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Domain\Exception\ForbiddenException;

final readonly class DeleteCommentHandler implements CommandHandlerInterface {
  public function __construct(
    private CommentStoreRepositoryInterface $commentStore,
    private CommentRepositoryInterface $commentRepository,
    private ImageRepositoryInterface $imageRepository,
  ) {
  }

  public function __invoke(DeleteCommentCommand $command, string $commentId, string $userId): void {
    $comment = $this->commentStore->get(ID::fromString($commentId));
    $commentView = $this->commentRepository->oneById($commentId);
    $image = $this->imageRepository->oneById($commentView->getImageId());

    $isOwner = $comment->isOwnedBy(ID::fromString($userId));
    $isImageOwner = $image->getOwner()?->getUuid() === $userId;

    if (!$isOwner && !$isImageOwner) {
      throw new ForbiddenException('You can only delete your own comments or comments on your images');
    }

    $comment->delete();

    $this->commentStore->store($comment);
  }
}
