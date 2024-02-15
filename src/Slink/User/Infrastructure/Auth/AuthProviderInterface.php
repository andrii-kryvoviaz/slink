<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth;

use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\Email;
use Ramsey\Uuid\UuidInterface;

interface AuthProviderInterface {
  /**
   * @param UuidInterface $uuid
   * @param Email $email
   * @param HashedPassword $hashedPassword
   * @return string
   */
  public function generateAccessToken(UuidInterface $uuid, Email $email, HashedPassword $hashedPassword): string;
  
  /**
   * @param int|null $ttl
   * @return string
   */
  public function generateRefreshToken(?int $ttl = 2592000): string;
}