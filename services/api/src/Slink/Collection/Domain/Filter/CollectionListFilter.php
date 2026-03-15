<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\Filter;

final readonly class CollectionListFilter {
  public function __construct(
    private ?int    $limit = 12,
    private ?string $userId = null,
    private ?string $searchTerm = null,
    private ?string $cursor = null,
  ) {
  }

  public function getLimit(): ?int {
    return $this->limit;
  }

  public function getUserId(): ?string {
    return $this->userId;
  }

  public function getSearchTerm(): ?string {
    return $this->searchTerm;
  }

  public function getCursor(): ?string {
    return $this->cursor;
  }
}
