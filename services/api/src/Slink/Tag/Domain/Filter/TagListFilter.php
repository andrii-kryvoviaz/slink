<?php

declare(strict_types=1);

namespace Slink\Tag\Domain\Filter;

final readonly class TagListFilter {
  public function __construct(
    private ?int $limit = 50,
    private ?string $orderBy = 'name',
    private ?string $order = 'asc',
    private ?string $userId = null,
    private ?string $parentId = null,
    private ?string $searchTerm = null,
    private ?bool $rootOnly = null,
    private ?bool $includeChildren = false,
    private ?string $cursor = null
  ) {}

  public function getLimit(): ?int {
    return $this->limit;
  }

  public function getOrderBy(): ?string {
    return $this->orderBy;
  }

  public function getOrder(): ?string {
    return $this->order;
  }

  public function getUserId(): ?string {
    return $this->userId;
  }

  public function getParentId(): ?string {
    return $this->parentId;
  }

  public function getSearchTerm(): ?string {
    return $this->searchTerm;
  }

  public function isRootOnly(): ?bool {
    return $this->rootOnly;
  }

  public function shouldIncludeChildren(): ?bool {
    return $this->includeChildren;
  }

  public function getCursor(): ?string {
    return $this->cursor;
  }
}