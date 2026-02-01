<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Query\GetCollectionItems;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetCollectionItemsQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    private string $collectionId = '',
    private int $limit = 12,
    private ?string $cursor = null,
  ) {
  }

  public function withCollectionId(string $collectionId): self {
    return new self($collectionId, $this->limit, $this->cursor);
  }

  public function getCollectionId(): string {
    return $this->collectionId;
  }

  public function getLimit(): int {
    return $this->limit;
  }

  public function getCursor(): ?string {
    return $this->cursor;
  }
}
