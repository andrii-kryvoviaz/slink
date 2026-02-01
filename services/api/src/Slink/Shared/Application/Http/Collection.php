<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Http;


use Slink\Shared\Domain\ValueObject\CursorPaginatorResult;

final readonly class Collection {
  /**
   * @param int|null $page
   * @param int $limit
   * @param int $total
   * @param iterable<int, Item> $data
   * @param string|null $nextCursor
   * @param string|null $prevCursor
   */
  public function __construct(
    public ?int     $page,
    public int      $limit,
    public int      $total,
    public iterable $data,
    public ?string  $nextCursor = null,
    public ?string  $prevCursor = null
  ) {
  }

  public static function fromCursorPaginator(
    ?CursorPaginatorResult $paginator,
    int                    $limit,
    int                    $total
  ): self {
    return new self(
      page: null,
      limit: $limit,
      total: $total,
      data: $paginator->items ?? [],
      nextCursor: $paginator?->nextCursor ? (string)$paginator->nextCursor : null,
      prevCursor: $paginator?->previousCursor ? (string)$paginator->previousCursor : null
    );
  }

  /**
   * @param iterable<int, Item> $data
   */
  public static function fromPagePaginator(
    iterable $data,
    int      $page,
    int      $limit,
    int      $total
  ): self {
    return new self(
      page: $page,
      limit: $limit,
      total: $total,
      data: $data,
    );
  }
}