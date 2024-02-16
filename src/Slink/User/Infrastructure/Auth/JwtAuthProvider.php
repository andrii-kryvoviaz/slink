<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth;

use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Contracts\AuthProviderInterface;
use Slink\User\Domain\Contracts\UserInterface;
use Slink\User\Domain\ValueObject\Auth\TokenPair;
use Slink\User\Domain\ValueObject\Auth\TokenParams;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final readonly class JwtAuthProvider implements AuthProviderInterface {
  
  /**
   * @param JWTTokenManagerInterface $jwtManager
   */
  public function __construct(
    private readonly JWTTokenManagerInterface $jwtManager
  ) {}
  
  /**
   * @param TokenInterface $token
   * @return array<string, mixed> | false
   * @throws JWTDecodeFailureException
   */
  public function decodeToken(TokenInterface $token): array | false {
    return $this->jwtManager->decode($token);
  }
  
  /**
   * @param UserInterface $user
   * @param array<string, mixed> $payload
   * @return string
   */
  public function generateAccessToken(UserInterface $user, array $payload = []): string {
    return $this->jwtManager->createFromPayload(Auth::createFromUser($user), $payload);
  }
  
  /**
   * @param ?int $ttl
   * @return string
   */
  public function generateRefreshToken(?int $ttl = 2592000): string {
    return sprintf('%s.%s', ID::generate()->toString(), time() + $ttl);
  }
  
  /**
   * @param UserInterface $user
   * @param ?TokenParams $params
   * @return TokenPair
   */
  #[\Override]
  public function generateTokenPair(UserInterface $user, ?TokenParams $params = null): TokenPair {
    if(!$params) {
      $params = TokenParams::create();
    }
    
    $accessToken = $this->generateAccessToken($user, $params->getPayload());
    $refreshToken = $this->generateRefreshToken($params->getTtl());
    
    return TokenPair::fromTokens($accessToken, $refreshToken);
  }
}