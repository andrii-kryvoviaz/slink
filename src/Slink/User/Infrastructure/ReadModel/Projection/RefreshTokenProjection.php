<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\Projection;

use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;
use Slink\User\Domain\Event\Auth\RefreshTokenIssued;
use Slink\User\Domain\Repository\RefreshTokenRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\RefreshTokenView;

final class RefreshTokenProjection extends AbstractProjection {
  public function __construct(private readonly RefreshTokenRepositoryInterface $repository) {
  }
  
  public function handleRefreshTokenIssued(RefreshTokenIssued $event): void {
    $refreshToken = RefreshTokenView::fromEvent($event);
    
    $this->repository->add($refreshToken);
  }
}