<?php

declare(strict_types=1);

namespace Slink\User\Domain\Repository;

use Slink\User\Infrastructure\ReadModel\View\UserView;

interface GetUserByRefreshTokenInterface {
  public function getUserByRefreshToken(string $hashedRefreshToken): UserView;
}