<?php

declare(strict_types=1);

namespace Slink\Bookmark\Application\Query\GetBookmarkStatus;

use Slink\Bookmark\Domain\Repository\BookmarkRepositoryInterface;
use Slink\Shared\Application\Query\QueryHandlerInterface;

final readonly class GetBookmarkStatusHandler implements QueryHandlerInterface {
  public function __construct(
    private BookmarkRepositoryInterface $bookmarkRepository,
  ) {
  }

  public function __invoke(GetBookmarkStatusQuery $query, string $userId): array {
    $status = $this->bookmarkRepository->getBookmarkStatus($query->imageId, $userId);

    return $status->toPayload();
  }
}
