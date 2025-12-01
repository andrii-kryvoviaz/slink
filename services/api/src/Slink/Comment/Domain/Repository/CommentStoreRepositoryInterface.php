<?php

declare(strict_types=1);

namespace Slink\Comment\Domain\Repository;

use Slink\Comment\Domain\Comment;
use Slink\Shared\Domain\ValueObject\ID;

interface CommentStoreRepositoryInterface {
  public function store(Comment $comment): void;

  public function get(ID $id): Comment;
}
