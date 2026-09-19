<?php

declare(strict_types=1);

namespace Slink\Notification\Domain\Filter;

use Slink\Notification\Domain\Enum\NotificationType;

final readonly class NotificationListFilter {
  public function __construct(
    private ?NotificationType $type = null,
    private bool $unreadOnly = false,
  ) {
  }

  public function getType(): ?NotificationType {
    return $this->type;
  }

  public function isUnreadOnly(): bool {
    return $this->unreadOnly;
  }
}
