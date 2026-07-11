<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\ValueObject;

/**
 * @template T
 */
final readonly class CursorPaginatorResult {
  /**
   * @param iterable<int, T> $items
   * @param Cursor|null $nextCursor
   * @param Cursor|null $previousCursor
   */
  public function __construct(
    public iterable $items,
    public ?Cursor  $nextCursor = null,
    public ?Cursor  $previousCursor = null,
  ) {
  }
}