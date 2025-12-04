<?php

declare(strict_types=1);

namespace Slink\Bookmark\Domain\ValueObject;

final readonly class BookmarkStatus {
  public function __construct(
    public bool $isBookmarked,
    public int $bookmarkCount,
  ) {
  }

  public function toPayload(): array {
    return [
      'isBookmarked' => $this->isBookmarked,
      'bookmarkCount' => $this->bookmarkCount,
    ];
  }
}
