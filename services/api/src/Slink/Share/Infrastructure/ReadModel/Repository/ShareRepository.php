<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\ReadModel\Repository;

use Override;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Domain\ValueObject\TargetPath;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;

final class ShareRepository extends AbstractRepository implements ShareRepositoryInterface {
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
    $entityManager = $this->getEntityManager();

    foreach ($this->findAllByShareable($shareableId, $shareableType) as $share) {
      $entityManager->remove($share);
    }
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
  }
}
