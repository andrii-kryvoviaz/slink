<?php

declare(strict_types=1);

namespace Slik\Image\Infrastructure\Repository;

use Slik\Image\Domain\Image;
use Slik\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slik\Shared\Domain\ValueObject\ID;
use Slik\Shared\Infrastructure\Persistence\EventStore\AbstractStoreRepository;

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