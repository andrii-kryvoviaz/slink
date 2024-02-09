<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Repository;

use Slink\Image\Domain\Image;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\EventStore\AbstractStoreRepository;

final class ImageStore extends AbstractStoreRepository implements ImageStoreRepositoryInterface {
  static function getAggregateRootClass(): string {
    return Image::class;
  }
  
  public function get(ID $id): Image {
    $image = $this->retrieve($id);
    if (!$image instanceof Image) {
      throw new \RuntimeException('Expected instance of Image, got ' . get_class($image));
    }
    return $image;
  }
  
  public function store(Image $image): void {
    $this->persist($image);
  }
}