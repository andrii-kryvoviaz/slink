<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Query\GetUserCollections;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetUserCollectionsQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    private string $userId,
    private int $page = 1,
    private int $limit = 12,
  ) {
  }

  public function getUserId(): string {
    return $this->userId;
  }

  public function getPage(): int {
    return $this->page;
  }

  public function getLimit(): int {
    return $this->limit;
  }
}
