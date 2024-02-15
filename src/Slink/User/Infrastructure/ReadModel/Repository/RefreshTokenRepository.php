<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\Repository;

use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;
use Slink\User\Domain\Repository\RefreshTokenRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\RefreshTokenView;

final class RefreshTokenRepository extends AbstractRepository implements RefreshTokenRepositoryInterface {
  
  #[\Override]
  public function add(RefreshTokenView $refreshTokenView): void {
    $this->_em->persist($refreshTokenView);
  }
  
  #[\Override]
  static protected function entityClass(): string {
    return RefreshTokenView::class;
  }
}