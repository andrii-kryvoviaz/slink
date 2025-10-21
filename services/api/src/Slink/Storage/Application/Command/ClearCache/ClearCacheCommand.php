<?php

declare(strict_types=1);

namespace Slink\Storage\Application\Command\ClearCache;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class ClearCacheCommand implements CommandInterface {
  use EnvelopedMessage;
}
