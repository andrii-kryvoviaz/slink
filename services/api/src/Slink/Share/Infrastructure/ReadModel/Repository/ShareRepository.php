<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\ReadModel\Repository;

use Override;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
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
  public function findById(string $id): ?ShareView {
    return $this->createQueryBuilder('s')
      ->where('s.uuid = :id')
      ->setParameter('id', $id)
      ->getQuery()
      ->getOneOrNullResult();
  }

  #[Override]
  public function findByTargetUrl(string $targetUrl): ?ShareView {
    return $this->createQueryBuilder('s')
      ->where('s.targetUrl = :targetUrl')
      ->setParameter('targetUrl', $targetUrl)
      ->getQuery()
      ->getOneOrNullResult();
  }
}
