<?php

declare(strict_types=1);

namespace Slink\User\Domain\Repository;

use Slink\User\Infrastructure\ReadModel\View\RefreshTokenView;

interface RefreshTokenRepositoryInterface {
  
  public function add(RefreshTokenView $refreshTokenView): void;
  public function remove(string $hashedRefreshToken): void;
}