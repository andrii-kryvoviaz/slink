<?php

declare(strict_types=1);

namespace Slik\User\Infrastructure\Auth;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Ramsey\Uuid\UuidInterface;
use Slik\User\Domain\ValueObject\Auth\HashedPassword;
use Slik\User\Domain\ValueObject\Email;

final readonly class JwtAuthProvider implements AuthProviderInterface {
  
  public function __construct(
    private readonly JWTTokenManagerInterface $JWTManager
  ) {}
  
  public function generateToken(UuidInterface $uuid, Email $email, HashedPassword $hashedPassword): string {
    return $this->JWTManager->create(Auth::create($uuid, $email, $hashedPassword));
  }
}