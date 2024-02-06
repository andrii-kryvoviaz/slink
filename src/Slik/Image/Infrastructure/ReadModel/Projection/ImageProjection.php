<?php

declare(strict_types=1);

namespace Slik\Image\Infrastructure\ReadModel\Projection;

use Doctrine\ORM\NonUniqueResultException;
use Slik\Image\Domain\Event\ImageAttributesWasUpdated;
use Slik\Image\Domain\Event\ImageWasCreated;
use Slik\Image\Domain\Repository\ImageRepositoryInterface;
use Slik\Image\Infrastructure\ReadModel\View\ImageView;
use Slik\Shared\Infrastructure\Exception\NotFoundException;
use Slik\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;

final class ImageProjection extends AbstractProjection {
  public function __construct(
    private readonly ImageRepositoryInterface $repository,
  ) {
  }
  
  public function handleImageWasCreated(ImageWasCreated $event): void {
    $image = ImageView::fromEvent($event);
    
    $this->repository->add($image);
  }
  
  /**
   * @throws NotFoundException
   * @throws NonUniqueResultException
   */
  public function handleImageAttributesWasUpdated(ImageAttributesWasUpdated $event): void {
    $image = $this->repository->oneById($event->id->toString());
    
    $image->merge(ImageView::fromEvent($event));
  }
}