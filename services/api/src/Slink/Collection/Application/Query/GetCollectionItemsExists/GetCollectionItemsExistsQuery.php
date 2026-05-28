<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Query\GetCollectionItemsExists;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetCollectionItemsExistsQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    private string $collectionId = '',
  ) {
  }

  public function withCollectionId(string $collectionId): self {
    return new self($collectionId);
  }

  public function getCollectionId(): string {
    return $this->collectionId;
  }
}
