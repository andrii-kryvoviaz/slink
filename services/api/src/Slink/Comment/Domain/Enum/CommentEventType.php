<?php

declare(strict_types=1);

namespace Slink\Comment\Domain\Enum;

enum CommentEventType: string {
  case Created = 'comment_created';
  case Edited = 'comment_edited';
  case Deleted = 'comment_deleted';
}
