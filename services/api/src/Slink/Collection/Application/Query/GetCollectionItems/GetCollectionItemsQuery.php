<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Query\GetCollectionItems;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetCollectionItemsQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    private string $collectionId,
    private int $page = 1,
    private int $limit = 20,
  ) {
  }

  public function getCollectionId(): string {
    return $this->collectionId;
  }

  public function getPage(): int {
    return $this->page;
  }

  public function getLimit(): int {
    return $this->limit;
  }
}
