<?php

declare(strict_types=1);

namespace Slink\Comment\Domain\Exception;

use Slink\Shared\Domain\Exception\NotFoundException;

final class CommentNotFoundException extends NotFoundException {
  public function __construct(string $id) {
    parent::__construct(sprintf('Comment with ID "%s" not found', $id));
  }
}
