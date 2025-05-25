<?php

declare(strict_types=1);

namespace Slink\User\Domain\Contracts;

use Slink\User\Domain\ValueObject\Auth\TokenPair;
use Slink\User\Domain\ValueObject\Auth\TokenParams;

interface AuthProviderInterface {
  /**
   * @param UserInterface $user
   * @param array<string, mixed> $payload
   * @return string
   */
  public function generateAccessToken(UserInterface $user, array $payload = []): string;
  
  /**
   * @param int|null $ttl
   * @return string
   */
  public function generateRefreshToken(?int $ttl = 2592000): string;
  
  /**
   * @param UserInterface $user
   * @param TokenParams|null $params
   * @return TokenPair
   */
  public function generateTokenPair(UserInterface $user, ?TokenParams $params = null): TokenPair;
}