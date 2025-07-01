<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ReadModel\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Override;
use Slink\Image\Domain\Filter\ImageListFilter;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Pagination\CursorPaginationTrait;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;

final class ImageRepository extends AbstractRepository implements ImageRepositoryInterface {
  use CursorPaginationTrait;

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
   * @param int $page
   * @param ImageListFilter $imageListFilter
   * @return Paginator<ImageView>
   */
  #[Override]
  public function geImageList(int $page, ImageListFilter $imageListFilter): Paginator {
    $qb = $this->_em
      ->createQueryBuilder()
      ->from(ImageView::class, 'image')
      ->leftJoin('image.user', 'user')
      ->select('
        image'
      );

    if ($limit = $imageListFilter->getLimit()) {
      $qb->setMaxResults($limit + 1);

      if ($cursor = $imageListFilter->getCursor()) {
        $this->applyCursorPagination(
          $qb,
          $cursor,
          $imageListFilter->getOrderBy() ?? 'attributes.createdAt',
          $imageListFilter->getOrder() ?? 'desc',
          'uuid',
          'image'
        );
      } else {
        $qb->setFirstResult(($page - 1) * $limit);
      }
    }

    if (($isPublic = $imageListFilter->getIsPublic()) !== null) {
      $qb->andWhere('image.attributes.isPublic = :isPublic')
        ->setParameter('isPublic', $isPublic);
    }

    if ($userId = $imageListFilter->getUserId()) {
      $qb->andWhere('image.user = :user')
        ->setParameter('user', $userId);
    }

    if ($uuids = $imageListFilter->getUuids()) {
      $qb->andWhere('image.uuid IN (:uuids)')
        ->setParameter('uuids', $uuids);
    }

    if ($searchTerm = $imageListFilter->getSearchTerm()) {
      $searchBy = $imageListFilter->getSearchBy();

      if ($searchBy === 'user') {
        $qb->andWhere('LOWER(user.username) LIKE LOWER(:searchTerm) OR LOWER(user.displayName) LIKE LOWER(:searchTerm)')
          ->setParameter('searchTerm', '%' . $searchTerm . '%');
      } elseif ($searchBy === 'description') {
        $qb->andWhere('LOWER(image.attributes.description) LIKE LOWER(:searchTerm)')
          ->setParameter('searchTerm', '%' . $searchTerm . '%');
      } elseif ($searchBy === 'hashtag') {
        $hashtagTerm = ltrim($searchTerm, '#');
        $qb->andWhere('LOWER(image.attributes.description) LIKE LOWER(:hashtagTerm)')
          ->setParameter('hashtagTerm', '%#' . $hashtagTerm . '%');
      } else {
        $qb->andWhere(
          'LOWER(user.username) LIKE LOWER(:searchTerm) OR LOWER(user.displayName) LIKE LOWER(:searchTerm) OR LOWER(image.attributes.description) LIKE LOWER(:searchTerm)'
        )->setParameter('searchTerm', '%' . $searchTerm . '%');
      }
    }

    $qb->orderBy('image.' . $imageListFilter->getOrderBy(), $imageListFilter->getOrder());
    $qb->addOrderBy('image.uuid', $imageListFilter->getOrder());

    return new Paginator($qb);
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
   * @param ImageView $image
   * @return void
   */
  public function remove(ImageView $image): void {
    $this->_em->remove($image);
  }
}