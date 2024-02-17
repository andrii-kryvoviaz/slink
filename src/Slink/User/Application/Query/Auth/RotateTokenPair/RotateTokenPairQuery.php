<?php

declare(strict_types=1);

namespace Slink\User\Application\Query\Auth\RotateTokenPair;

use Slink\Shared\Application\Query\QueryInterface;

final readonly class RotateTokenPairQuery implements QueryInterface {
  
  public function __construct(
    private string $refreshToken,
  ) {}
  
  public function getRefreshToken(): string {
    return $this->refreshToken;
  }
}