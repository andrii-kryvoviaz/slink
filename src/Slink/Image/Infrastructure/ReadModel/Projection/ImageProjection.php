<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ReadModel\Projection;

use Doctrine\ORM\NonUniqueResultException;
use Slink\Image\Domain\Event\ImageAttributesWasUpdated;
use Slink\Image\Domain\Event\ImageWasCreated;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;

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