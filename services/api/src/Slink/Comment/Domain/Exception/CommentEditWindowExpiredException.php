<?php

declare(strict_types=1);

namespace Slink\Comment\Domain\Exception;

use Slink\Shared\Domain\Exception\ForbiddenException;

final class CommentEditWindowExpiredException extends ForbiddenException {
  public function __construct() {
    parent::__construct('Comment edit window has expired. Comments can only be edited within 24 hours of creation.');
  }
}
