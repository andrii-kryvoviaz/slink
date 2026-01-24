<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\Service;

use Jcupitt\Vips\Image as VipsImage;
use Slink\Collection\Domain\Service\CollageBuilderInterface;
use Slink\Collection\Domain\Service\CollectionCoverGeneratorInterface;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Throwable;

#[AsAlias(CollectionCoverGeneratorInterface::class)]
final class CollectionCoverGenerator implements CollectionCoverGeneratorInterface {
  private const string COVER_FORMAT = 'avif';
  private const int MAX_IMAGES = 5;

  public function __construct(
    private readonly StorageInterface $storage,
    private readonly ImageRepositoryInterface $imageRepository,
    private readonly CollageBuilderInterface $collageBuilder,
  ) {
  }

  /**
   * @param string[] $imageIds
   */
  public function getCoverUrl(string $collectionId, array $imageIds): ?string {
    if (empty($imageIds)) {
      return null;
    }

    return sprintf('/api/collection/%s/cover', $collectionId);
  }

  /**
   * @param string[] $imageIds
   */
  public function getCoverContent(string $collectionId, array $imageIds): ?string {
    if (empty($imageIds)) {
      return null;
    }

    $fileName = $this->getCoverFileName($collectionId);

    return $this->storage->readFromCache($fileName)
      ?? $this->generateAndCache($fileName, $imageIds);
  }

  public function invalidateCover(string $collectionId): void {
    $this->storage->deleteFromCache($this->getCoverFileName($collectionId));
  }

  /**
   * @param string[] $imageIds
   */
  private function generateAndCache(string $fileName, array $imageIds): ?string {
    $images = $this->loadImages($imageIds);
    $content = $this->collageBuilder->build($images);

    if ($content === null) {
      return null;
    }

    $this->storage->writeToCache($fileName, $content);

    return $content;
  }

  /**
   * @param string[] $imageIds
   * @return VipsImage[]
   */
  private function loadImages(array $imageIds): array {
    $images = [];

    foreach (array_slice($imageIds, 0, self::MAX_IMAGES) as $imageId) {
      $image = $this->loadImage($imageId);

      if ($image !== null) {
        $images[] = $image;
      }
    }

    return $images;
  }

  private function loadImage(string $imageId): ?VipsImage {
    try {
      $imageView = $this->imageRepository->oneById($imageId);

      if (str_starts_with($imageView->getMimeType(), 'image/svg')) {
        return null;
      }

      $content = $this->storage->getImage(ImageOptions::fromPayload([
        'fileName' => $imageView->getFileName(),
        'mimeType' => $imageView->getMimeType(),
      ]));

      return $content ? VipsImage::newFromBuffer($content) : null;
    } catch (Throwable) {
      return null;
    }
  }

  private function getCoverFileName(string $collectionId): string {
    return sprintf('%s.%s', $collectionId, self::COVER_FORMAT);
  }
}
