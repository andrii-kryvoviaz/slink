<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Http;


use Slink\Shared\Infrastructure\Exception\NotFoundException;

final readonly class Collection {
  /**
   * @param int $page
   * @param int $limit
   * @param int $total
   * @param array<int, Item> $data
   * @throws NotFoundException
   */
  public function __construct(
    public int $page,
    public int $limit,
    public int $total,
    public array $data
  ) {
    $this->exists($page, $limit, $total);
  }
  
  /**
   * @throws NotFoundException
   */
  private function exists(int $page, int $limit, int $total): void {
    if (($limit * ($page - 1)) >= $total) {
      throw new NotFoundException();
    }
  }
}