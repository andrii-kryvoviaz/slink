<?php

declare(strict_types=1);

namespace Slink\Comment\Application\Command\UpdateComment;

use Slink\Comment\Domain\Repository\CommentStoreRepositoryInterface;
use Slink\Comment\Domain\ValueObject\CommentContent;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Domain\Exception\ForbiddenException;

final readonly class UpdateCommentHandler implements CommandHandlerInterface {
  public function __construct(
    private CommentStoreRepositoryInterface $commentStore,
  ) {
  }

  public function __invoke(UpdateCommentCommand $command, string $commentId, string $userId): void {
    $comment = $this->commentStore->get(ID::fromString($commentId));

    if (!$comment->isOwnedBy(ID::fromString($userId))) {
      throw new ForbiddenException('You can only edit your own comments');
    }

    $content = CommentContent::fromString($command->getContent());
    $comment->edit($content);

    $this->commentStore->store($comment);
  }
}
