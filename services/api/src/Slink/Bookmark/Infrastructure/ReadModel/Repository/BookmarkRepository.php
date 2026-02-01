<?php

declare(strict_types=1);

namespace Slink\Bookmark\Infrastructure\ReadModel\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Override;
use Slink\Bookmark\Domain\Repository\BookmarkRepositoryInterface;
use Slink\Bookmark\Domain\ValueObject\BookmarkStatus;
use Slink\Bookmark\Infrastructure\ReadModel\View\BookmarkView;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Pagination\CursorPaginationTrait;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;

final class BookmarkRepository extends AbstractRepository implements BookmarkRepositoryInterface {
  use CursorPaginationTrait;

  #[Override]
  protected static function entityClass(): string {
    return BookmarkView::class;
  }

  #[Override]
  public function add(BookmarkView $bookmark): void {
    $this->getEntityManager()->persist($bookmark);
  }

  #[Override]
  public function remove(BookmarkView $bookmark): void {
    $this->getEntityManager()->remove($bookmark);
  }

  /**
   * @throws NotFoundException
   * @throws NonUniqueResultException
   */
  #[Override]
  public function oneById(string $id): BookmarkView {
    $bookmark = $this->findById($id);

    if ($bookmark === null) {
      throw new NotFoundException('Bookmark not found');
    }

    return $bookmark;
  }

  #[Override]
  public function findById(string $id): ?BookmarkView {
    return $this->createQueryBuilder('b')
      ->where('b.uuid = :id')
      ->setParameter('id', $id)
      ->getQuery()
      ->getOneOrNullResult();
  }

  #[Override]
  public function findByUserIdAndImageId(string $userId, string $imageId): ?BookmarkView {
    return $this->createQueryBuilder('b')
      ->join('b.user', 'u')
      ->join('b.image', 'i')
      ->where('u.uuid = :userId')
      ->andWhere('i.uuid = :imageId')
      ->setParameter('userId', $userId)
      ->setParameter('imageId', $imageId)
      ->getQuery()
      ->getOneOrNullResult();
  }

  #[Override]
  public function findByUserId(string $userId, int $limit, ?string $cursor = null): Paginator {
    $qb = $this->createQueryBuilder('b')
      ->join('b.user', 'u')
      ->leftJoin('b.image', 'i')
      ->where('u.uuid = :userId')
      ->setParameter('userId', $userId)
      ->orderBy('b.createdAt', 'DESC')
      ->setMaxResults($limit + 1);

    if ($cursor !== null) {
      $this->applyCursorPagination($qb, $cursor, 'createdAt', 'desc', 'uuid', 'b');
    }

    return new Paginator($qb->getQuery());
  }

  #[Override]
  public function countByUserId(string $userId): int {
    return (int) $this->createQueryBuilder('b')
      ->select('COUNT(b.uuid)')
      ->join('b.user', 'u')
      ->where('u.uuid = :userId')
      ->setParameter('userId', $userId)
      ->getQuery()
      ->getSingleScalarResult();
  }

  #[Override]
  public function countByImageId(string $imageId): int {
    return (int) $this->createQueryBuilder('b')
      ->select('COUNT(b.uuid)')
      ->join('b.image', 'i')
      ->where('i.uuid = :imageId')
      ->setParameter('imageId', $imageId)
      ->getQuery()
      ->getSingleScalarResult();
  }

  #[Override]
  public function isBookmarkedByUser(string $imageId, string $userId): bool {
    $result = $this->createQueryBuilder('b')
      ->select('COUNT(b.uuid)')
      ->join('b.user', 'u')
      ->join('b.image', 'i')
      ->where('i.uuid = :imageId')
      ->andWhere('u.uuid = :userId')
      ->setParameter('imageId', $imageId)
      ->setParameter('userId', $userId)
      ->getQuery()
      ->getSingleScalarResult();

    return (int) $result > 0;
  }

  /**
   * @param array<string> $imageIds
   * @return array<string>
   */
  #[Override]
  public function getBookmarkedImageIds(string $userId, array $imageIds): array {
    if (empty($imageIds)) {
      return [];
    }

    $results = $this->createQueryBuilder('b')
      ->select('i.uuid')
      ->join('b.user', 'u')
      ->join('b.image', 'i')
      ->where('u.uuid = :userId')
      ->andWhere('i.uuid IN (:imageIds)')
      ->setParameter('userId', $userId)
      ->setParameter('imageIds', $imageIds)
      ->getQuery()
      ->getScalarResult();

    return array_column($results, 'uuid');
  }

  #[Override]
  public function findByImageId(string $imageId, int $limit, ?string $cursor = null): Paginator {
    $qb = $this->createQueryBuilder('b')
      ->join('b.image', 'i')
      ->leftJoin('b.user', 'u')
      ->where('i.uuid = :imageId')
      ->setParameter('imageId', $imageId)
      ->orderBy('b.createdAt', 'DESC')
      ->setMaxResults($limit + 1);

    if ($cursor !== null) {
      $this->applyCursorPagination($qb, $cursor, 'createdAt', 'desc', 'uuid', 'b');
    }

    return new Paginator($qb->getQuery());
  }

  #[Override]
  public function getBookmarkStatus(string $imageId, string $userId): BookmarkStatus {
    $result = $this->createQueryBuilder('b')
      ->select(
        'COUNT(b.uuid) as totalCount',
        'SUM(CASE WHEN u.uuid = :userId THEN 1 ELSE 0 END) as userBookmarked'
      )
      ->join('b.image', 'i')
      ->leftJoin('b.user', 'u')
      ->where('i.uuid = :imageId')
      ->setParameter('imageId', $imageId)
      ->setParameter('userId', $userId)
      ->getQuery()
      ->getSingleResult();

    return new BookmarkStatus(
      isBookmarked: (int) $result['userBookmarked'] > 0,
      bookmarkCount: (int) $result['totalCount'],
    );
  }
}
