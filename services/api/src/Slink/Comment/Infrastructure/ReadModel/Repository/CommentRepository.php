<?php

declare(strict_types=1);

namespace Slink\Comment\Infrastructure\ReadModel\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Override;
use Slink\Comment\Domain\Exception\CommentNotFoundException;
use Slink\Comment\Domain\Repository\CommentRepositoryInterface;
use Slink\Comment\Infrastructure\ReadModel\View\CommentView;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;

final class CommentRepository extends AbstractRepository implements CommentRepositoryInterface {
  #[Override]
  protected static function entityClass(): string {
    return CommentView::class;
  }

  #[Override]
  public function add(CommentView $comment): void {
    $this->_em->persist($comment);
  }

  #[Override]
  public function oneById(string $id): CommentView {
    $qb = $this->createQueryBuilder('c')
      ->where('c.uuid = :id')
      ->setParameter('id', $id);

    try {
      return $this->oneOrException($qb);
    } catch (\Exception) {
      throw new CommentNotFoundException($id);
    }
  }

  #[Override]
  public function findByImageId(string $imageId, int $page = 1, int $limit = 20): Paginator {
    $qb = $this->createQueryBuilder('c')
      ->join('c.image', 'i')
      ->where('i.uuid = :imageId')
      ->setParameter('imageId', $imageId)
      ->orderBy('c.createdAt', 'ASC');

    return $this->paginate($qb, $page, $limit);
  }

  #[Override]
  public function countByImageId(string $imageId): int {
    return (int) $this->createQueryBuilder('c')
      ->select('COUNT(c.uuid)')
      ->join('c.image', 'i')
      ->where('i.uuid = :imageId')
      ->setParameter('imageId', $imageId)
      ->getQuery()
      ->getSingleScalarResult();
  }
}
