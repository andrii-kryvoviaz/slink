<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Query\GetUserCollections;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetUserCollectionsQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    private int     $limit = 12,
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
