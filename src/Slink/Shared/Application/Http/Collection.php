<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Http;


final readonly class Collection {
  /**
   * @param int $page
   * @param int $limit
   * @param int $total
   * @param array<int, Item> $data
   */
  public function __construct(
    public int $page,
    public int $limit,
    public int $total,
    public array $data
  ) {
  }
}