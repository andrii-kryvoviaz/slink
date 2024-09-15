<?php

declare(strict_types=1);

namespace Slink\User\Application\Query\User\GetUserList;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetUserListQuery implements QueryInterface {
  use EnvelopedMessage;
  
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
