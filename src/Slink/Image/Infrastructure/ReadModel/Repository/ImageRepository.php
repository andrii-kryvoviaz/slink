<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ReadModel\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;

final class ImageRepository extends AbstractRepository implements ImageRepositoryInterface {
  public function add(ImageView $image): void {
    $this->_em->persist($image);
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
  
  static protected function entityClass(): string {
    return ImageView::class;
  }
}