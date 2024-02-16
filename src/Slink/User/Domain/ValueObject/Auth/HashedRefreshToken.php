<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\Auth;

use Ramsey\Uuid\Uuid;
use Slink\Shared\Domain\Exception\DateTimeException;
use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\Shared\Domain\ValueObject\DateTime;
use Slink\User\Domain\Exception\InvalidRefreshToken;

final readonly class HashedRefreshToken extends AbstractValueObject {
  /**
   * @param string $hashedRefreshToken
   * @param DateTime $expiresAt
   * @throws DateTimeException
   */
  private function __construct(
    private string $hashedRefreshToken,
    private DateTime $expiresAt,
  ) {
    if($expiresAt->isBefore(DateTime::now())) {
      throw new InvalidRefreshToken();
    }
  }
  
  /**
   * @param string $hashedRefreshToken
   * @param string $expiresAt
   * @return self
   * @throws DateTimeException
   */
  public static function createFromHashed(string $hashedRefreshToken, string $expiresAt): self {
    return new self($hashedRefreshToken, DateTime::fromString($expiresAt));
  }
  
  /**
   * @param string $plainRefreshToken
   * @return self
   * @throws DateTimeException
   */
  public static function encode(#[\SensitiveParameter] string $plainRefreshToken): self {
    if(!str_contains($plainRefreshToken, '.')) {
      throw new InvalidRefreshToken();
    }
    
    [$refreshToken, $expiresAt] = explode('.', $plainRefreshToken);
    
    if(!Uuid::isValid($refreshToken)) {
      throw new InvalidRefreshToken();
    }
    
    return new self(self::hash($plainRefreshToken), DateTime::fromTimestamp((int) $expiresAt));
  }
  
  /**
   * @param string $plainRefreshToken
   * @return string
   */
  private static function hash(#[\SensitiveParameter] string $plainRefreshToken): string {
    return hash('sha256', $plainRefreshToken);
  }
  
  /**
   * @return bool
   * @throws DateTimeException
   */
  public function isExpired(): bool {
    return $this->expiresAt->isBefore(DateTime::now());
  }
  
  /**
   * @return DateTime
   */
  public function getExpiresAt(): DateTime {
    return $this->expiresAt;
  }
  
  /**
   * @return string
   */
  #[\Override]
  public function toString(): string {
    return $this->hashedRefreshToken;
  }
}