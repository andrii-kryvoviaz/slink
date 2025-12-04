<?php

declare(strict_types=1);

namespace Slink\Bookmark\Application\Query\GetImageBookmarkers;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetImageBookmarkersQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    public int $page = 1,
    public string $imageId = '',
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
