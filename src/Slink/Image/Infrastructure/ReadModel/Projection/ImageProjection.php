<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ReadModel\Projection;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Slink\Image\Domain\Event\ImageAttributesWasUpdated;
use Slink\Image\Domain\Event\ImageWasCreated;
use Slink\Image\Domain\Event\ImageWasDeleted;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\Event\EventWithEntityManager;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;

final class ImageProjection extends AbstractProjection {
  /**
   * @param ImageRepositoryInterface $repository
   * @param EntityManagerInterface $em
   */
  public function __construct(
    private readonly ImageRepositoryInterface $repository,
    private readonly EntityManagerInterface $em
  ) {
  }
  
  /**
   * @param ImageWasCreated $event
   * @return void
   */
  public function handleImageWasCreated(ImageWasCreated $event): void {
    $eventWithEntityManager = EventWithEntityManager::decorate($event, $this->em);
    $image = ImageView::fromEvent($eventWithEntityManager);
    
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
  
  /**
   * @param ImageWasDeleted $event
   * @throws NotFoundException
   * @throws NonUniqueResultException
   * @return void
   */
  public function handleImageWasDeleted(ImageWasDeleted $event): void {
    $image = $this->repository->oneById($event->id->toString());
    
    $this->repository->remove($image);
  }
}