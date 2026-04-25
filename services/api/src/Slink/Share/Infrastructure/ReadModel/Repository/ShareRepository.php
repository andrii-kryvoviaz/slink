<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\ReadModel\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Override;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionView;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Share\Domain\Enum\ShareExpiryFilter;
use Slink\Share\Domain\Enum\ShareProtectionFilter;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Filter\ShareListFilter;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Domain\ValueObject\TargetPath;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Infrastructure\Pagination\CursorPaginationTrait;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;

final class ShareRepository extends AbstractRepository implements ShareRepositoryInterface {
  use CursorPaginationTrait;

  #[Override]
  protected static function entityClass(): string {
    return ShareView::class;
  }

  #[Override]
  public function add(ShareView $share): void {
    $this->getEntityManager()->persist($share);
  }

  #[Override]
  public function remove(ShareView $share): void {
    $this->getEntityManager()->remove($share);
  }

  #[Override]
  public function findById(string $id): ?ShareView {
    return $this->createQueryBuilder('s')
      ->where('s.uuid = :id')
      ->setParameter('id', $id)
      ->getQuery()
      ->getOneOrNullResult();
  }

  #[Override]
  public function findByTargetPath(TargetPath $targetPath): ?ShareView {
    return $this->createQueryBuilder('s')
      ->where('s.targetUrl = :targetUrl')
      ->setParameter('targetUrl', $targetPath->toString())
      ->getQuery()
      ->getOneOrNullResult();
  }

  #[Override]
  public function findByShareable(string $shareableId, ShareableType $shareableType): ?ShareView {
    return $this->createQueryBuilder('s')
      ->where('s.shareable.shareableId = :shareableId')
      ->andWhere('s.shareable.shareableType = :shareableType')
      ->setParameter('shareableId', $shareableId)
      ->setParameter('shareableType', $shareableType)
      ->setMaxResults(1)
      ->getQuery()
      ->getOneOrNullResult();
  }

  #[Override]
  public function removeByShareable(string $shareableId, ShareableType $shareableType): void {
    $this->getEntityManager()
      ->createQuery(
        'DELETE FROM ' . ShareView::class . ' s
         WHERE s.shareable.shareableId = :shareableId
           AND s.shareable.shareableType = :shareableType'
      )
      ->execute([
        'shareableId' => $shareableId,
        'shareableType' => $shareableType,
      ]);
  }

  #[Override]
  public function findAllByShareable(string $shareableId, ShareableType $shareableType): array {
    return $this->createQueryBuilder('s')
      ->where('s.shareable.shareableId = :shareableId')
      ->andWhere('s.shareable.shareableType = :shareableType')
      ->setParameter('shareableId', $shareableId)
      ->setParameter('shareableType', $shareableType)
      ->orderBy('s.createdAt', 'DESC')
      ->getQuery()
      ->getResult();
  }

  #[Override]
  public function findAllUnpublished(): iterable {
    return $this->createQueryBuilder('s')
      ->where('s.accessControl.isPublished = :isPublished')
      ->setParameter('isPublished', false)
      ->getQuery()
      ->toIterable();
  }

  /**
   * @param ShareListFilter $filter
   * @return Paginator<ShareView>
   */
  #[Override]
  public function getShareList(ShareListFilter $filter): Paginator {
    $qb = $this->buildShareListQuery($filter);

    if ($limit = $filter->getLimit()) {
      $qb->setMaxResults($limit + 1);

      if ($cursor = $filter->getCursor()) {
        $this->applyCursorPagination(
          $qb,
          $cursor,
          $filter->getOrderBy() ?? 'createdAt',
          $filter->getOrder() ?? 'desc',
          'uuid',
          's'
        );
      }
    }

    $qb->orderBy('s.' . ($filter->getOrderBy() ?? 'createdAt'), $filter->getOrder() ?? 'desc')
      ->addOrderBy('s.uuid', $filter->getOrder() ?? 'desc');

    return new Paginator($qb);
  }

  #[Override]
  public function countShareList(ShareListFilter $filter): int {
    $qb = $this->buildShareListQuery($filter)
      ->select('COUNT(DISTINCT s.uuid)');

    return (int) $qb->getQuery()->getSingleScalarResult();
  }

  #[Override]
  public function existsByFilter(ShareListFilter $filter): bool {
    $qb = $this->buildShareListQuery($filter)
      ->select('1')
      ->setMaxResults(1);

    return (bool) $qb->getQuery()->getOneOrNullResult();
  }

  private function buildShareListQuery(ShareListFilter $filter): QueryBuilder {
    $qb = $this->getEntityManager()
      ->createQueryBuilder()
      ->from(ShareView::class, 's')
      ->select('s')
      ->where('s.accessControl.isPublished = :isPublished')
      ->setParameter('isPublished', true);

    if ($type = $filter->getType()) {
      $qb->andWhere('s.shareable.shareableType = :type')
        ->setParameter('type', $type);
    }

    if ($shareableId = $filter->getShareableId()) {
      $qb->andWhere('s.shareable.shareableId = :shareableId')
        ->setParameter('shareableId', $shareableId);

      if ($shareableType = $filter->getShareableType()) {
        $qb->andWhere('s.shareable.shareableType = :shareableType')
          ->setParameter('shareableType', $shareableType);
      }
    }

    if ($userId = $filter->getUserId()) {
      $this->applyUserScope($qb, $userId);
    }

    if ($searchTerm = trim($filter->getSearchTerm() ?? '')) {
      $this->applySearchFilter($qb, $searchTerm);
    }

    $this->applyExpiryFilter($qb, $filter->getExpiry());
    $this->applyProtectionFilter($qb, $filter->getProtection());

    return $qb;
  }

  private function applyUserScope(QueryBuilder $qb, string $userId): void {
    $qb->andWhere(
      $qb->expr()->orX(
        $qb->expr()->andX(
          's.shareable.shareableType = :userScopeImageType',
          $qb->expr()->in('s.shareable.shareableId', $this->userImageIdsDql()),
        ),
        $qb->expr()->andX(
          's.shareable.shareableType = :userScopeCollectionType',
          $qb->expr()->in('s.shareable.shareableId', $this->userCollectionIdsDql()),
        ),
      )
    )
      ->setParameter('userId', $userId)
      ->setParameter('userScopeImageType', ShareableType::Image)
      ->setParameter('userScopeCollectionType', ShareableType::Collection);
  }

  private function applySearchFilter(QueryBuilder $qb, string $searchTerm): void {
    $qb->andWhere(
      $qb->expr()->orX(
        $qb->expr()->andX(
          's.shareable.shareableType = :searchImageType',
          $qb->expr()->in('s.shareable.shareableId', $this->imageIdsBySearchDql()),
        ),
        $qb->expr()->andX(
          's.shareable.shareableType = :searchCollectionType',
          $qb->expr()->in('s.shareable.shareableId', $this->collectionIdsBySearchDql()),
        ),
      )
    )
      ->setParameter('searchTerm', '%' . $searchTerm . '%')
      ->setParameter('searchImageType', ShareableType::Image)
      ->setParameter('searchCollectionType', ShareableType::Collection);
  }

  private function applyExpiryFilter(QueryBuilder $qb, ?ShareExpiryFilter $expiry): void {
    match ($expiry) {
      null, ShareExpiryFilter::Any => null,
      ShareExpiryFilter::HasExpiry => $qb
        ->andWhere('s.accessControl.expiresAt IS NOT NULL')
        ->andWhere('s.accessControl.expiresAt > :now')
        ->setParameter('now', DateTime::now()),
      ShareExpiryFilter::Expired => $qb
        ->andWhere('s.accessControl.expiresAt IS NOT NULL')
        ->andWhere('s.accessControl.expiresAt <= :now')
        ->setParameter('now', DateTime::now()),
      ShareExpiryFilter::NoExpiry => $qb
        ->andWhere('s.accessControl.expiresAt IS NULL'),
    };
  }

  private function applyProtectionFilter(QueryBuilder $qb, ?ShareProtectionFilter $protection): void {
    match ($protection) {
      null, ShareProtectionFilter::Any => null,
      ShareProtectionFilter::PasswordProtected => $qb
        ->andWhere('s.accessControl.passwordHash IS NOT NULL'),
      ShareProtectionFilter::NoPassword => $qb
        ->andWhere('s.accessControl.passwordHash IS NULL'),
    };
  }

  private function userImageIdsDql(): string {
    return $this->getEntityManager()->createQueryBuilder()
      ->select('img.uuid')
      ->from(ImageView::class, 'img')
      ->where('img.user = :userId')
      ->getDQL();
  }

  private function userCollectionIdsDql(): string {
    return $this->getEntityManager()->createQueryBuilder()
      ->select('col.uuid')
      ->from(CollectionView::class, 'col')
      ->where('col.user = :userId')
      ->getDQL();
  }

  private function imageIdsBySearchDql(): string {
    return $this->getEntityManager()->createQueryBuilder()
      ->select('searchImg.uuid')
      ->from(ImageView::class, 'searchImg')
      ->where('LOWER(searchImg.attributes.fileName) LIKE LOWER(:searchTerm)')
      ->getDQL();
  }

  private function collectionIdsBySearchDql(): string {
    return $this->getEntityManager()->createQueryBuilder()
      ->select('searchCol.uuid')
      ->from(CollectionView::class, 'searchCol')
      ->where('LOWER(searchCol.name) LIKE LOWER(:searchTerm)')
      ->getDQL();
  }
}
