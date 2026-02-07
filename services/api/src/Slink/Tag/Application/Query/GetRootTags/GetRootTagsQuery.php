<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Query\GetRootTags;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetRootTagsQuery implements QueryInterface {
  use EnvelopedMessage;
}
