<?php

declare(strict_types=1);

namespace Slik\Image\Infrastructure\ReadModel\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Slik\Image\Infrastructure\ReadModel\View\ImageView;
use Slik\Shared\Infrastructure\Exception\NotFoundException;
use Slik\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;

final class ImageRepository extends AbstractRepository {
  
  public function add(ImageView $image): void {
    $this->_em->persist($image);
  }
  
  static protected function entityClass(): string {
    return ImageView::class;
  }
  
  /**
   * @throws NotFoundException
   * @throws NonUniqueResultException
   */
  public function oneById(string $id): ImageView {
    $qb = $this->_em
      ->createQueryBuilder()
      ->from(ImageView::class, 'image')
      ->select('
        image'
      )
      ->where('image.uuid = :id')
      ->setParameter('id', $id);
    
    return $this->oneOrException($qb);
  }
}