<?php

declare(strict_types=1);

namespace Slik\User\Infrastructure\Auth;

use Slik\User\Domain\ValueObject\Auth\HashedPassword;
use Slik\User\Domain\ValueObject\Email;
use Ramsey\Uuid\UuidInterface;

interface AuthProviderInterface {
  public function generateToken(UuidInterface $uuid, Email $email, HashedPassword $hashedPassword): string;
}