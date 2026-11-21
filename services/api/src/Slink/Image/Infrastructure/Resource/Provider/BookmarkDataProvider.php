<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Resource\Provider;

use Slink\Bookmark\Domain\Repository\BookmarkRepositoryInterface;
use Slink\Bookmark\Domain\ValueObject\BookmarkStatus;
use Slink\Image\Application\Resource\ImageDataProviderInterface;
use Slink\Image\Infrastructure\Resource\ImageResourceContext;
use Slink\Shared\Application\Resource\ResourceContextInterface;
use Slink\Shared\Domain\Enum\ResourceProviderTag;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(ResourceProviderTag::Image->value)]
final readonly class BookmarkDataProvider implements ImageDataProviderInterface {
  public function __construct(
    private BookmarkRepositoryInterface $repository,
  ) {
  }

  public function getProviderKey(): string {
    return 'bookmarks';
  }

  public function supports(ResourceContextInterface $context): bool {
    return $context->hasGroup('bookmark') && $context instanceof ImageResourceContext;
  }

  /**
   * @param ImageResourceContext $context
   * @return array<string, BookmarkStatus>
   */
  public function fetch(ResourceContextInterface $context): array {
    $counts = $this->repository->countByImageIds($context->imageIds);
    $bookmarked = array_flip($this->bookmarkedImageIds($context));
    $statuses = [];

    foreach ($context->imageIds as $imageId) {
      $statuses[$imageId] = new BookmarkStatus(
        isBookmarked: isset($bookmarked[$imageId]),
        bookmarkCount: $counts[$imageId] ?? 0,
      );
    }

    return $statuses;
  }

  /**
   * @return array<string>
   */
  private function bookmarkedImageIds(ImageResourceContext $context): array {
    if ($context->viewerUserId === null) {
      return [];
    }

    return $this->repository->getBookmarkedImageIds($context->viewerUserId, $context->imageIds);
  }
}
