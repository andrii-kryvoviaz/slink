<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\Auth;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;

final readonly class TokenPair extends AbstractValueObject {
  /**
   * @param string $accessToken
   * @param string $refreshToken
   */
  private function __construct(
    #[\SensitiveParameter]
    private string $accessToken,
    
    #[\SensitiveParameter]
    private string $refreshToken,
  ) {
  }
  
  /**
   * @param string $accessToken
   * @param string $refreshToken
   * @return self
   */
  public static function fromTokens(string $accessToken, string $refreshToken): self {
    return new self($accessToken, $refreshToken);
  }
  
  /**
   * @return string
   */
  public function getAccessToken(): string {
    return $this->accessToken;
  }
  
  /**
   * @return string
   */
  public function getRefreshToken(): string {
    return $this->refreshToken;
  }
}