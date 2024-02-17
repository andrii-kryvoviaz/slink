<?php

declare(strict_types=1);

namespace Slink\User\Domain\Repository;

use Ramsey\Uuid\UuidInterface;
use Slink\User\Domain\ValueObject\Auth\HashedRefreshToken;

interface CheckUserByRefreshTokenInterface {
  public function existsByRefreshToken(HashedRefreshToken $hashedRefreshToken): ?UuidInterface;
}