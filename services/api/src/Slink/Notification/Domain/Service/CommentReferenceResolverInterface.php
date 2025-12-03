<?php

declare(strict_types=1);

namespace Slink\Notification\Domain\Service;

interface CommentReferenceResolverInterface {
  public function getCommentAuthorId(string $commentId): ?string;
}
