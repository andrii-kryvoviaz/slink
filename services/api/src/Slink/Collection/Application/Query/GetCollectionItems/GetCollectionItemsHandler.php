<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Query\GetCollectionItems;

use Slink\Collection\Domain\Enum\ItemType;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionItemView;
use Slink\Image\Domain\Filter\ImageListFilter;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;

final readonly class GetCollectionItemsHandler implements QueryHandlerInterface {
  public function __construct(
    private CollectionItemRepositoryInterface $collectionItemRepository,
    private ImageRepositoryInterface $imageRepository,
  ) {
  }

  public function __invoke(GetCollectionItemsQuery $query): Collection {
    $items = $this->collectionItemRepository->getByCollectionIdPaginated(
      $query->getCollectionId(),
      $query->getPage(),
      $query->getLimit()
    );

    $total = $this->collectionItemRepository->countByCollectionId($query->getCollectionId());
    $images = $this->fetchImagesForItems($items);

    return new Collection(
      $query->getPage(),
      $query->getLimit(),
      $total,
      array_map(fn(CollectionItemView $item) => $this->mapItemToResponse($item, $images), $items)
    );
  }

  /**
   * @param CollectionItemView[] $items
   * @return array<string, Item>
   */
  private function fetchImagesForItems(array $items): array {
    $imageIds = array_map(
      fn(CollectionItemView $item) => $item->getItemId(),
      array_filter($items, fn(CollectionItemView $item) => $item->getItemType() === ItemType::Image)
    );

    if (empty($imageIds)) {
      return [];
    }

    $images = [];
    $imageList = $this->imageRepository->geImageList(1, new ImageListFilter(uuids: $imageIds));

    foreach ($imageList as $image) {
      $images[$image->getUuid()] = Item::fromEntity($image);
    }

    return $images;
  }

  /**
   * @param array<string, Item> $images
   */
  private function mapItemToResponse(CollectionItemView $item, array $images): Item {
    $extra = [];

    if ($item->getItemType() === ItemType::Image && isset($images[$item->getItemId()])) {
      $extra['image'] = $images[$item->getItemId()]->resource;
    }

    return Item::fromEntity($item, $extra, groups: ['public']);
  }
}
