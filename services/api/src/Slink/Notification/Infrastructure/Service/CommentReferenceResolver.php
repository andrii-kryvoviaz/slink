<?php

declare(strict_types=1);

namespace Slink\Notification\Infrastructure\Service;

use Slink\Comment\Domain\Repository\CommentRepositoryInterface;
use Slink\Notification\Domain\Service\CommentReferenceResolverInterface;

final readonly class CommentReferenceResolver implements CommentReferenceResolverInterface {
  public function __construct(
    private CommentRepositoryInterface $commentRepository,
  ) {
  }

  public function getCommentAuthorId(string $commentId): ?string {
    try {
      $comment = $this->commentRepository->oneById($commentId);
      return $comment->getUserId();
    } catch (\Exception) {
      return null;
    }
  }
}
