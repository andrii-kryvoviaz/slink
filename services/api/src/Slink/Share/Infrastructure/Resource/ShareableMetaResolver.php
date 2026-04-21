<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\Resource;

use Closure;
use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;

final readonly class ShareableMetaResolver {
  public function __construct(
    private ImageRepositoryInterface $imageRepository,
    private CollectionRepositoryInterface $collectionRepository,
  ) {}

  /**
   * @param list<ShareView> $shares
   * @return Closure(string, ShareableType): array{id: string, name: string, previewUrl: ?string}
   */
  public function resolver(array $shares): Closure {
    $imageIds = [];
    $collectionIds = [];

    foreach ($shares as $share) {
      $ref = $share->getShareable();
      $id = $ref->getShareableId();

      match ($ref->getShareableType()) {
        ShareableType::Image => $imageIds[$id] = true,
        ShareableType::Collection => $collectionIds[$id] = true,
      };
    }

    $imageMeta = [];
    foreach ($this->imageRepository->findByIds(array_keys($imageIds)) as $image) {
      $fileName = $image->getFileName();
      $imageMeta[$image->getUuid()] = [
        'id' => $image->getUuid(),
        'name' => $fileName,
        'previewUrl' => '/image/' . $fileName,
      ];
    }

    $collectionMeta = [];
    foreach ($this->collectionRepository->findByIds(array_keys($collectionIds)) as $collection) {
      $collectionMeta[$collection->getId()] = [
        'id' => $collection->getId(),
        'name' => $collection->getName(),
        'previewUrl' => null,
      ];
    }

    return static fn(string $id, ShareableType $type): array => match ($type) {
      ShareableType::Image => $imageMeta[$id] ?? ['id' => $id, 'name' => $id, 'previewUrl' => null],
      ShareableType::Collection => $collectionMeta[$id] ?? ['id' => $id, 'name' => $id, 'previewUrl' => null],
    };
  }
}
