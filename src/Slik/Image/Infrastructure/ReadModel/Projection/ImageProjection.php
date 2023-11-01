<?php

declare(strict_types=1);

namespace Slik\Image\Infrastructure\ReadModel\Projection;

use Slik\Image\Domain\Event\ImageWasCreated;
use Slik\Image\Infrastructure\ReadModel\Repository\ImageRepository;
use Slik\Image\Infrastructure\ReadModel\View\ImageView;
use Slik\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;

final class ImageProjection extends AbstractProjection {
  public function __construct(
    private readonly ImageRepository $repository,
  ) {
  }
  
  public function handleImageWasCreated(ImageWasCreated $event): void {
    $image = ImageView::fromEvent($event);
    
    $this->repository->add($image);
  }
}