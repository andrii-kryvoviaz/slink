<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service;

use Slink\Image\Domain\Service\ImageRetrievalInterface;
use Slink\Image\Domain\Service\ImageTransformerInterface;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageCacheInterface;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(ImageRetrievalInterface::class)]
final readonly class ImageRetrievalService implements ImageRetrievalInterface {
  public function __construct(
    private StorageInterface $storage,
    private StorageCacheInterface $cache,
    private ImageTransformerInterface $imageTransformer,
  ) {
  }

  public function getImage(ImageOptions $image): ?string {
    if (!$image->isModified()) {
      return $this->storage->readImage($image->getFileName());
    }

    return $this->getTransformedImage($image);
  }

  private function getTransformedImage(ImageOptions $image): ?string {
    $cacheFileName = $image->getCacheFileName();

    $cached = $this->cache->readFromCache($cacheFileName);
    if ($cached !== null) {
      return $cached;
    }

    $originalContent = $this->storage->readImage($image->getFileName());
    if ($originalContent === null) {
      return null;
    }

    $transformed = $this->imageTransformer->transform($originalContent, $image);
    $this->cache->writeToCache($cacheFileName, $transformed);

    return $transformed;
  }
}
