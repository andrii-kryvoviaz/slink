<?php

declare(strict_types=1);

namespace Slink\Comment\Domain\Service;

use Slink\Shared\Domain\ValueObject\Date\DateTime;

final class CommentEditPolicy {
  private const int EDIT_WINDOW_HOURS = 24;

  public static function canEdit(DateTime $createdAt): bool {
    $now = DateTime::now();
    $deadline = $createdAt->modify(sprintf('+%d hours', self::EDIT_WINDOW_HOURS));

    return $now->isBefore($deadline);
  }

  public static function getEditDeadline(DateTime $createdAt): DateTime {
    return $createdAt->modify(sprintf('+%d hours', self::EDIT_WINDOW_HOURS));
  }
}
