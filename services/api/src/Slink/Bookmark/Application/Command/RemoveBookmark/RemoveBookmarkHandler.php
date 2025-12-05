<?php

declare(strict_types=1);

namespace Slink\Bookmark\Application\Command\RemoveBookmark;

use Slink\Bookmark\Domain\Repository\BookmarkRepositoryInterface;
use Slink\Bookmark\Domain\Repository\BookmarkStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class RemoveBookmarkHandler implements CommandHandlerInterface {
  public function __construct(
    private BookmarkStoreRepositoryInterface $bookmarkStore,
    private BookmarkRepositoryInterface $bookmarkRepository,
  ) {
  }

  public function __invoke(RemoveBookmarkCommand $command, string $userId): void {
    $bookmarkView = $this->bookmarkRepository->findByUserIdAndImageId($userId, $command->imageId);

    if ($bookmarkView === null) {
      return;
    }

    $bookmark = $this->bookmarkStore->get(ID::fromString($bookmarkView->getId()));
    $bookmark->remove();

    $this->bookmarkStore->store($bookmark);
  }
}
