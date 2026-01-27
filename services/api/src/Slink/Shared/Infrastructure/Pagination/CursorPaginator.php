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
   * @return CursorPaginatorResult|null A Cursor object if there are more entities, otherwise null.
   * @throws \Exception
   */
  public function paginate(iterable $entities, int $limit): ?CursorPaginatorResult {
    /** @var \Iterator $iterator */
    $iterator = is_array($entities) ? new \ArrayIterator($entities) : $entities;
    $items = new \ArrayIterator();

    while ($iterator->valid() && $iterator->key() < $limit) {
      $items->append($iterator->current());
      $iterator->next();
    }

    if (!$items->valid()) {
      return null;
    }

    $last = $iterator->current() ?? $items->current();

    return new CursorPaginatorResult(
      $items,
      $this->generateNextCursor($last),
    );
  }
}