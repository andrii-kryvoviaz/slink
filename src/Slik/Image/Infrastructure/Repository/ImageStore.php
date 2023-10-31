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
    return $this->retrieve($id);
  }
  
  public function store(Image $image): void {
    $this->persist($image);
  }
}