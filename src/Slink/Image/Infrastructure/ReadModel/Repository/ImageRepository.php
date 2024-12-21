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
  
  /**
   * @param ImageView $image
   * @return void
   */
  public function add(ImageView $image): void {
    $this->_em->persist($image);
  }
  
  /**
   * @param ImageView $image
   * @return void
   */
  public function remove(ImageView $image): void {
    $this->_em->remove($image);
  }
  
  /**
   * @throws NotFoundException
   * @throws NonUniqueResultException
   */
  public function oneById(string $id, ?string $extension = null): ImageView {
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
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function oneByFileName(string $fileName): ImageView {
    $qb = $this->_em
      ->createQueryBuilder()
      ->from(ImageView::class, 'image')
      ->select('
        image'
      )
      ->where('image.attributes.fileName = :fileName')
      ->setParameter('fileName', $fileName);
    
    return $this->oneOrException($qb);
  }
  
  /**
   * @param int $page
   * @param ImageListFilter $imageListFilter
   * @return Paginator<ImageView>
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