<?php

declare(strict_types=1);

namespace Slink\Notification\Application\Command\MarkNotificationRead;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class MarkNotificationReadCommand implements CommandInterface {
  use EnvelopedMessage;
}
