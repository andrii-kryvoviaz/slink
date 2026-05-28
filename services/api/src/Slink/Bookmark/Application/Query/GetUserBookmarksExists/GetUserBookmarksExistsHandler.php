<?php

declare(strict_types=1);

namespace Slink\Bookmark\Application\Query\GetUserBookmarksExists;

use Slink\Bookmark\Domain\Repository\BookmarkRepositoryInterface;
use Slink\Shared\Application\Query\QueryHandlerInterface;

final readonly class GetUserBookmarksExistsHandler implements QueryHandlerInterface {
  public function __construct(
    private BookmarkRepositoryInterface $repository,
  ) {
  }

  public function __invoke(GetUserBookmarksExistsQuery $query, string $userId): bool {
    return $this->repository->existsByUserId($userId);
  }
}
