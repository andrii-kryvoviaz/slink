<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\ReadModel\Repository;

use Override;
use Slink\Share\Domain\Repository\ShortUrlRepositoryInterface;
use Slink\Share\Infrastructure\ReadModel\View\ShortUrlView;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;

final class ShortUrlRepository extends AbstractRepository implements ShortUrlRepositoryInterface {
  #[Override]
  protected static function entityClass(): string {
    return ShortUrlView::class;
  }

  #[Override]
  public function add(ShortUrlView $shortUrl): void {
    $this->getEntityManager()->persist($shortUrl);
  }

  #[Override]
  public function findByShortCode(string $shortCode): ?ShortUrlView {
    return $this->createQueryBuilder('s')
      ->where('s.shortCode = :shortCode')
      ->setParameter('shortCode', $shortCode)
      ->getQuery()
      ->getOneOrNullResult();
  }

  #[Override]
  public function existsByShortCode(string $shortCode): bool {
    return $this->createQueryBuilder('s')
      ->select('1')
      ->where('s.shortCode = :shortCode')
      ->setParameter('shortCode', $shortCode)
      ->getQuery()
      ->getOneOrNullResult() !== null;
  }
}
