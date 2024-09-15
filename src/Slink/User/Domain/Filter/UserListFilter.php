<?php

declare(strict_types=1);

namespace Slink\User\Domain\Filter;

use Slink\User\Application\Query\User\GetUserList\GetUserListQuery;

final readonly class UserListFilter {
  /**
   * @param int $limit
   * @param string $orderBy
   * @param string $order
   * @param string|null $search
   */
  public function __construct(
    private int $limit = 10,
    private string $orderBy = 'createdAt',
    private string $order = 'desc',
    private ?string $search = null
  ) {
  }
  
  /**
   * @param GetUserListQuery $query
   * @return self
   */
  public static function fromQuery(GetUserListQuery $query): self {
    return new self(
      limit: $query->getLimit(),
      orderBy: $query->getOrderBy(),
      order: $query->getOrder(),
      search: $query->getSearch()
    );
  }
  
  /**
   * @return int
   */
  public function getLimit(): int {
    return $this->limit;
  }
  
  /**
   * @return string
   */
  public function getOrderBy(): string {
    return $this->orderBy;
  }
  
  /**
   * @return string
   */
  public function getOrder(): string {
    return $this->order;
  }
  
  /**
   * @return string|null
   */
  public function getSearch(): ?string {
    return $this->search;
  }
}
