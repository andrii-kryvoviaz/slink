<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Pagination;

use Slink\Shared\Application\Http\Item;
use Slink\Shared\Domain\ValueObject\CursorPaginatorResult;

final readonly class CursorPaginator {
  use CursorPaginationTrait;

  /**
   * Paginate entities and return a subset based on the limit.
   *
   * @param iterable<Item> $entities The collection of entities to paginate.
   * @param int $limit The maximum number of entities to return.
   * @return CursorPaginatorResult|null A CursorPaginatorResult object or null if no items.
   * @throws \Exception
   */
  public function paginate(iterable $entities, int $limit): ?CursorPaginatorResult {
    /** @var \Iterator $iterator */
    $iterator = is_array($entities) ? new \ArrayIterator($entities) : $entities;
    $items = new \ArrayIterator();
    $lastItem = null;

    while ($iterator->valid() && $items->count() < $limit) {
      $lastItem = $iterator->current();
      $items->append($lastItem);
      $iterator->next();
    }

    if ($items->count() === 0) {
      return null;
    }

    $hasMore = $iterator->valid();

    return new CursorPaginatorResult(
      $items,
      $hasMore ? $this->generateNextCursor($lastItem) : null,
    );
  }
}