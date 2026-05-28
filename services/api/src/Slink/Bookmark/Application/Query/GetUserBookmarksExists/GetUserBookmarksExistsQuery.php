<?php

declare(strict_types=1);

namespace Slink\Bookmark\Application\Query\GetUserBookmarksExists;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetUserBookmarksExistsQuery implements QueryInterface {
  use EnvelopedMessage;
}
