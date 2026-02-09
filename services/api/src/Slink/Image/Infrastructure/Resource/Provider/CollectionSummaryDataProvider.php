<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Resource\Provider;

use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Image\Application\Resource\ImageDataProviderInterface;
use Slink\Image\Infrastructure\Resource\ImageResourceContext;
use Slink\Shared\Application\Resource\ResourceContextInterface;
use Slink\Shared\Domain\Enum\ResourceProviderTag;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(ResourceProviderTag::Image->value)]
final readonly class CollectionSummaryDataProvider implements ImageDataProviderInterface {
  public function __construct(
    private CollectionItemRepositoryInterface $collectionItemRepository,
    private CollectionRepositoryInterface     $collectionRepository,
  ) {
  }

  public function getProviderKey(): string {
    return 'collectionSummaries';
  }

  public function supports(ResourceContextInterface $context): bool {
    return $context->hasGroup('collection') && $context instanceof ImageResourceContext;
  }

  /**
   * @param ImageResourceContext $context
   * @return array<string, array<int, array{id: string, name: string}>>
   */
  public function fetch(ResourceContextInterface $context): array {
    $imageIds = $context->imageIds;

    if (count($imageIds) === 0) {
      return [];
    }

    $imageCollections = $this->collectionItemRepository->getCollectionIdsByImageIds($imageIds);

    if (count($imageCollections) === 0) {
      return [];
    }

    $collectionIdSet = [];
    foreach ($imageCollections as $collectionIds) {
      foreach ($collectionIds as $collectionId) {
        $collectionIdSet[$collectionId] = true;
      }
    }

    $collectionIds = array_keys($collectionIdSet);

    if (count($collectionIds) === 0) {
      return [];
    }

    $collections = $this->collectionRepository->findByIds($collectionIds);
    $collectionMap = [];

    foreach ($collections as $collection) {
      $collectionMap[$collection->getId()] = [
        'id' => $collection->getId(),
        'name' => $collection->getName(),
      ];
    }

    $result = [];
    foreach ($imageCollections as $imageId => $collectionIdsForImage) {
      $result[$imageId] = [];

      foreach ($collectionIdsForImage as $collectionId) {
        if (isset($collectionMap[$collectionId])) {
          $result[$imageId][] = $collectionMap[$collectionId];
        }
      }
    }

    return $result;
  }
}
