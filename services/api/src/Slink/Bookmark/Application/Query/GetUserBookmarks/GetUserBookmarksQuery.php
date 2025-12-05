<?php

declare(strict_types=1);

namespace Slink\Bookmark\Application\Query\GetUserBookmarks;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetUserBookmarksQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    public int $page = 1,
    private int $limit = 10,
    private ?string $cursor = null,
  ) {
  }

  public function getLimit(): int {
    return $this->limit;
  }

  public function getCursor(): ?string {
    return $this->cursor;
  }
}
