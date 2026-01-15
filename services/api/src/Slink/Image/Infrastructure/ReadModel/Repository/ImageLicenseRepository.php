<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ReadModel\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Slink\Image\Infrastructure\ReadModel\View\ImageLicenseView;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;

final class ImageLicenseRepository extends AbstractRepository {
  static protected function entityClass(): string {
    return ImageLicenseView::class;
  }

  public function add(ImageLicenseView $license): void {
    $this->getEntityManager()->persist($license);
  }

  public function remove(ImageLicenseView $license): void {
    $this->getEntityManager()->remove($license);
  }

  /**
   * @throws NotFoundException
   * @throws NonUniqueResultException
   */
  public function oneByImageId(string $imageId): ImageLicenseView {
    $qb = $this->getEntityManager()
      ->createQueryBuilder()
      ->from(ImageLicenseView::class, 'license')
      ->select('license')
      ->where('IDENTITY(license.image) = :imageId')
      ->setParameter('imageId', $imageId);

    return $this->oneOrException($qb);
  }

  public function findByImageId(string $imageId): ?ImageLicenseView {
    try {
      return $this->oneByImageId($imageId);
    } catch (NotFoundException|NonUniqueResultException) {
      return null;
    }
  }
}
