<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth;

use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\Email;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final readonly class JwtAuthProvider implements AuthProviderInterface {
  
  /**
   * @param JWTTokenManagerInterface $JWTManager
   */
  public function __construct(
    private readonly JWTTokenManagerInterface $JWTManager
  ) {}
  
  /**
   * @param TokenInterface $token
   * @return array<string, mixed> | false
   * @throws JWTDecodeFailureException
   */
  public function decodeToken(TokenInterface $token): array | false {
    return $this->JWTManager->decode($token);
  }
  
  /**
   * @param UuidInterface $uuid
   * @param Email $email
   * @param HashedPassword $hashedPassword
   * @return string
   */
  public function generateAccessToken(UuidInterface $uuid, Email $email, HashedPassword $hashedPassword): string {
    return $this->JWTManager->create(Auth::create($uuid, $email, $hashedPassword));
  }
  
  /**
   * @param ?int $ttl
   * @return string
   */
  public function generateRefreshToken(?int $ttl = 60 * 60 * 24 * 30): string {
    return sprintf('%s.%s', Uuid::uuid4()->toString(), time() + $ttl);
  }
}