<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Resource\Provider;

use Slink\Bookmark\Domain\Repository\BookmarkRepositoryInterface;
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
    return $context->hasGroup('bookmark')
      && $context instanceof ImageResourceContext
      && $context->viewerUserId !== null;
  }

  /**
   * @param ImageResourceContext $context
   * @return array<string, true>
   */
  public function fetch(ResourceContextInterface $context): array {
    if ($context->viewerUserId === null) {
      return [];
    }

    $bookmarkedIds = $this->repository->getBookmarkedImageIds($context->viewerUserId, $context->imageIds);

    return array_fill_keys($bookmarkedIds, true);
  }
}
