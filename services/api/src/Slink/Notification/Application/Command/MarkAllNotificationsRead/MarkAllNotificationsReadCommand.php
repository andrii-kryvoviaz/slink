<?php

declare(strict_types=1);

namespace Slink\Notification\Application\Command\MarkAllNotificationsRead;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class MarkAllNotificationsReadCommand implements CommandInterface {
  use EnvelopedMessage;
}
