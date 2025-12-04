<?php

declare(strict_types=1);

namespace Slink\Bookmark\Application\Query\GetBookmarkStatus;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetBookmarkStatusQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    public string $imageId,
  ) {
  }
}
