<?php

declare(strict_types=1);

namespace Slik\Image\Infrastructure\ReadModel\Repository;

use Slik\Image\Infrastructure\ReadModel\View\ImageView;
use Slik\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;

final class ImageRepository extends AbstractRepository {
  
  public function add(ImageView $image): void {
    $this->_em->persist($image);
  }
  
  static function entityClass(): string {
    return ImageView::class;
  }
}