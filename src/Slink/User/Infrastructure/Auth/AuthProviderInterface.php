<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth;

use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\Email;
use Ramsey\Uuid\UuidInterface;

interface AuthProviderInterface {
  public function generateAccessToken(UuidInterface $uuid, Email $email, HashedPassword $hashedPassword): string;
  
  public function generateRefreshToken(?int $ttl): string;
}