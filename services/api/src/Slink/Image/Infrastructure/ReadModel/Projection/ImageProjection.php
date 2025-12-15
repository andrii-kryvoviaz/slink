<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ReadModel\Projection;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Slink\Image\Domain\Event\ImageAttributesWasUpdated;
use Slink\Image\Domain\Event\ImageLicenseWasUpdated;
use Slink\Image\Domain\Event\ImageMetadataWasUpdated;
use Slink\Image\Domain\Event\ImageWasCreated;
use Slink\Image\Domain\Event\ImageWasDeleted;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\Repository\ImageLicenseRepository;
use Slink\Image\Infrastructure\ReadModel\View\ImageLicenseView;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\Event\EventWithEntityManager;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;
use Ramsey\Uuid\Uuid;

final class ImageProjection extends AbstractProjection {
  public function __construct(
    private readonly ImageRepositoryInterface $repository,
    private readonly ImageLicenseRepository $licenseRepository,
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

    if ($event->license !== null) {
      $imageLicense = new ImageLicenseView(
        Uuid::uuid4()->toString(),
        $image,
        $event->license
      );
      $this->licenseRepository->add($imageLicense);
    }
  }

  /**
   * @throws NotFoundException
   * @throws NonUniqueResultException
   */
  public function handleImageAttributesWasUpdated(ImageAttributesWasUpdated $event): void {
    $image = $this->repository->oneById($event->id->toString());

    $image->updateAttributes($event->attributes);
  }

  /**
   * @throws NotFoundException
   * @throws NonUniqueResultException
   */
  public function handleImageMetadataWasUpdated(ImageMetadataWasUpdated $event): void {
    $image = $this->repository->oneById($event->id->toString());

    $image->updateMetadata($event->metadata);
  }

  /**
   * @param ImageWasDeleted $event
   * @return void
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function handleImageWasDeleted(ImageWasDeleted $event): void {
    $image = $this->repository->oneById($event->id->toString());

    $this->repository->remove($image);
  }

  public function handleImageLicenseWasUpdated(ImageLicenseWasUpdated $event): void {
    $imageId = $event->id->toString();
    $image = $this->repository->oneById($imageId);
    $existingLicense = $this->licenseRepository->findByImageId($imageId);

    if ($event->license === null) {
      if ($existingLicense) {
        $this->licenseRepository->remove($existingLicense);
      }
      return;
    }

    if ($existingLicense) {
      $existingLicense->setLicense($event->license);
    } else {
      $licenseView = new ImageLicenseView(
        Uuid::uuid4()->toString(),
        $image,
        $event->license
      );
      $this->licenseRepository->add($licenseView);
    }
  }
}