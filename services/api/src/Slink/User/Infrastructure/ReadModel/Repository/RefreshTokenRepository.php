<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\Repository;

use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;
use Slink\User\Domain\Repository\RefreshTokenRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\RefreshTokenView;

final class RefreshTokenRepository extends AbstractRepository implements RefreshTokenRepositoryInterface {
  #[\Override]
  static protected function entityClass(): string {
    return RefreshTokenView::class;
  }
  
  #[\Override]
  public function add(RefreshTokenView $refreshTokenView): void {
    $this->getEntityManager()->persist($refreshTokenView);
  }
  
  #[\Override]
  public function remove(string $hashedRefreshToken): void {
    $record = $this->getEntityManager()->find(RefreshTokenView::class, $hashedRefreshToken);
    
    if ($record) {
      $this->getEntityManager()->remove($record);
    }
  }
}