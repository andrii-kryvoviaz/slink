<?php

declare(strict_types=1);

namespace Slink\Notification\Application\Query\GetUnreadCount;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetUnreadCountQuery implements QueryInterface {
  use EnvelopedMessage;
}
