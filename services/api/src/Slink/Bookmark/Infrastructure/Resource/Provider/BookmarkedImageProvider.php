<?php

declare(strict_types=1);

namespace Slink\Bookmark\Infrastructure\Resource\Provider;

use Slink\Bookmark\Infrastructure\Resource\BookmarkResourceContext;
use Slink\Image\Domain\Filter\ImageListFilter;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Infrastructure\Resource\ImageResourceContext;
use Slink\Image\Infrastructure\Resource\ImageResourceProcessor;
use Slink\Shared\Application\Resource\ResourceContextInterface;
use Slink\Shared\Application\Resource\ResourceDataProviderInterface;
use Slink\Shared\Domain\Enum\ResourceProviderTag;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(ResourceProviderTag::Bookmark->value)]
final readonly class BookmarkedImageProvider implements ResourceDataProviderInterface {
  public function __construct(
    private ImageRepositoryInterface $imageRepository,
    private ImageResourceProcessor   $imageResourceProcessor,
  ) {
  }

  public function getProviderKey(): string {
    return 'images';
  }

  public function supports(ResourceContextInterface $context): bool {
    if (!$context instanceof BookmarkResourceContext) {
      return false;
    }

    return $context->imageIds !== [];
  }

  /**
   * @param BookmarkResourceContext $context
   * @return array<string, array<string, mixed>>
   */
  public function fetch(ResourceContextInterface $context): array {
    $images = $this->imageRepository->geImageList(new ImageListFilter(
      limit: count($context->imageIds),
      isPublic: true,
      uuids: $context->imageIds,
    ));

    $items = $this->imageResourceProcessor->many($images, new ImageResourceContext(
      groups: ['public', 'bookmark', 'license'],
      viewerUserId: $context->viewerUserId,
    ));

    $result = [];

    foreach ($items as $item) {
      if (!is_array($item->resource)) {
        continue;
      }

      $result[$item->resource['id']] = $item->resource;
    }

    return $result;
  }
}
