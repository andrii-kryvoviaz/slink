<?php

declare(strict_types=1);

namespace Slink\Bookmark\Domain\ValueObject;

final readonly class BookmarkStatus {
  public function __construct(
    public bool $isBookmarked,
    public int $bookmarkCount,
  ) {
  }

  /**
   * @return array{isBookmarked: bool, bookmarkCount: int}
   */
  public function toPayload(): array {
    return [
      'isBookmarked' => $this->isBookmarked,
      'bookmarkCount' => $this->bookmarkCount,
    ];
  }
}
