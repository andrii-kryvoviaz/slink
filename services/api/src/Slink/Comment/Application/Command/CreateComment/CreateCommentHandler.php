<?php

declare(strict_types=1);

namespace Slink\Comment\Application\Command\CreateComment;

use Slink\Comment\Domain\Comment;
use Slink\Comment\Domain\Repository\CommentStoreRepositoryInterface;
use Slink\Comment\Domain\ValueObject\CommentContent;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class CreateCommentHandler implements CommandHandlerInterface {
  public function __construct(
    private CommentStoreRepositoryInterface $commentStore,
  ) {
  }

  public function __invoke(CreateCommentCommand $command, string $imageId, string $userId): ID {
    $commentId = ID::generate();
    $authorId = ID::fromString($userId);
    $referencedCommentId = $command->getReferencedCommentId()
      ? ID::fromString($command->getReferencedCommentId())
      : null;

    $content = CommentContent::fromString($command->getContent());

    $comment = Comment::create(
      $commentId,
      ID::fromString($imageId),
      $authorId,
      $referencedCommentId,
      $content,
    );

    $this->commentStore->store($comment);

    return $commentId;
  }
}
