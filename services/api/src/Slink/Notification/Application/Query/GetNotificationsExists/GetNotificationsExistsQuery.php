<?php

declare(strict_types=1);

namespace Slink\Notification\Application\Query\GetNotificationsExists;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetNotificationsExistsQuery implements QueryInterface {
  use EnvelopedMessage;
}
