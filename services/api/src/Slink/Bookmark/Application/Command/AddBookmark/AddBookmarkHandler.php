<?php

declare(strict_types=1);

namespace Slink\Bookmark\Application\Command\AddBookmark;

use Slink\Bookmark\Domain\Bookmark;
use Slink\Bookmark\Domain\Context\BookmarkCreationContext;
use Slink\Bookmark\Domain\Repository\BookmarkStoreRepositoryInterface;
use Slink\Bookmark\Domain\Specification\SelfBookmarkSpecification;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class AddBookmarkHandler implements CommandHandlerInterface {
  public function __construct(
    private BookmarkStoreRepositoryInterface $bookmarkStore,
    private ImageRepositoryInterface $imageRepository,
  ) {
  }

  public function __invoke(AddBookmarkCommand $command, string $userId): ID {
    $bookmarkId = ID::generate();
    $imageId = ID::fromString($command->imageId);
    $userId = ID::fromString($userId);

    $context = new BookmarkCreationContext(
      new SelfBookmarkSpecification($this->imageRepository),
    );
    
    $bookmark = Bookmark::create(
      $bookmarkId,
      $imageId,
      $userId,
      $context,
    );

    $this->bookmarkStore->store($bookmark);

    return $bookmarkId;
  }
}
