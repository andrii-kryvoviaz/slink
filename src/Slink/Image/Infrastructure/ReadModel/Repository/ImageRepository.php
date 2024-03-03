<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ReadModel\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Slink\Image\Domain\Filter\ImageListFilter;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;

final class ImageRepository extends AbstractRepository implements ImageRepositoryInterface {
  static protected function entityClass(): string {
    return ImageView::class;
  }
  
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
  
  /**
   * @param int $page
   * @param ImageListFilter $imageListFilter
   * @return Paginator
   */
  #[\Override]
  public function geImageList(int $page, ImageListFilter $imageListFilter): Paginator {
    $qb = $this->_em
      ->createQueryBuilder()
      ->from(ImageView::class, 'image')
      ->select('
        image'
      )
      ->setMaxResults($imageListFilter->getLimit())
      ->setFirstResult(($page - 1) * $imageListFilter->getLimit());
    
    if ($imageListFilter->getIsPublic() !== null) {
      $qb->andWhere('image.attributes.isPublic = :isPublic')
        ->setParameter('isPublic', $imageListFilter->getIsPublic());
    }
    
    if ($imageListFilter->getUserId() !== null) {
      $qb->andWhere('image.user = :user')
        ->setParameter('user', $imageListFilter->getUserId());
    }
    
    $qb->orderBy('image.' . $imageListFilter->getOrderBy(), $imageListFilter->getOrder());
    
    return new Paginator($qb);
  }
}