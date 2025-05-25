<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageList;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetImageListQuery implements QueryInterface {
  use EnvelopedMessage;
  public function __construct(
    private int $limit = 10,
    private string $orderBy = 'attributes.createdAt',
    private string $order = 'desc',
  ) {
  }
  
  public function getLimit(): int {
    return $this->limit;
  }
  
  public function getOrderBy(): string {
    return $this->orderBy;
  }
  
  public function getOrder(): string {
    return $this->order;
  }
}