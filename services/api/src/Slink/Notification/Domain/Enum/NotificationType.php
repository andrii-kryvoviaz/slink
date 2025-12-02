<?php

declare(strict_types=1);

namespace Slink\Notification\Domain\Enum;

enum NotificationType: string {
  case COMMENT = 'comment';
  case COMMENT_REPLY = 'comment_reply';
  case ADDED_TO_FAVORITE = 'added_to_favorite';

  public function getLabel(): string {
    return match ($this) {
      self::COMMENT => 'New comment on your image',
      self::COMMENT_REPLY => 'Someone replied to your comment',
      self::ADDED_TO_FAVORITE => 'Your image was added to favorites',
    };
  }
}
