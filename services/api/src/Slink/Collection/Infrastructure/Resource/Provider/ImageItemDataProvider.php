<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\Resource\Provider;

use Slink\Collection\Application\Resource\ItemDataProviderInterface;
use Slink\Collection\Domain\Enum\ItemType;
use Slink\Collection\Infrastructure\Resource\CollectionItemResourceContext;
use Slink\Image\Domain\Filter\ImageListFilter;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Resource\ResourceContextInterface;
use Slink\Shared\Domain\Enum\ResourceProviderTag;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(ResourceProviderTag::CollectionItem->value)]
final readonly class ImageItemDataProvider implements ItemDataProviderInterface {
  public function __construct(
    private ImageRepositoryInterface $imageRepository,
  ) {
  }

  public function getProviderKey(): string {
    return 'items';
  }

  public function supportsItemType(ItemType $type): bool {
    return $type === ItemType::Image;
  }

  public function supports(ResourceContextInterface $context): bool {
    if (!$context instanceof CollectionItemResourceContext) {
      return false;
    }

    return $context->hasItemsOfType(ItemType::Image);
  }

  /**
   * @param CollectionItemResourceContext $context
   * @return array<string, Item>
   */
  public function fetch(ResourceContextInterface $context): array {
    $imageIds = $context->getItemIdsByType(ItemType::Image);

    if (empty($imageIds)) {
      return [];
    }

    $images = $this->imageRepository->geImageList(new ImageListFilter(
      limit: count($imageIds),
      uuids: $imageIds
    ));
    $result = [];

    foreach ($images as $image) {
      $result[$image->getUuid()] = Item::fromEntity($image);
    }

    return $result;
  }
}
