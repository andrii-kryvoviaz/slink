<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\Auth;

use RuntimeException;
use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\User\Domain\Exception\InvalidPasswordException;

final readonly class HashedPassword extends AbstractValueObject {
  private const int COST = 12;
  
  /**
   * @param string $hashedPassword
   */
  private function __construct(private string $hashedPassword) {
  }
  
  /**
   * @param string $plainPassword
   * @return self
   */
  public static function encode(#[\SensitiveParameter] string $plainPassword): self {
    return new self(self::hash($plainPassword));
  }
  
  /**
   * @param string $hashedPassword
   * @return self
   */
  public static function fromHash(string $hashedPassword): self {
    return new self($hashedPassword);
  }
  
  /**
   * @param string $plainPassword
   * @return bool
   */
  public function match(#[\SensitiveParameter] string $plainPassword): bool {
    return \password_verify($plainPassword, $this->hashedPassword);
  }
  
  /**
   * @param string $plainPassword
   * @return string
   */
  private static function hash(#[\SensitiveParameter] string $plainPassword): string {
    if (\mb_strlen($plainPassword) < 6) {
      throw new InvalidPasswordException('Min 6 characters password');
    }
    
    $hashedPassword = \password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => self::COST]);

    if (!$hashedPassword) {
      throw new RuntimeException('Server error hashing password');
    }

    return (string) $hashedPassword;
  }
  
  /**
   * @return string
   */
  #[\Override]
  public function toString(): string {
    return $this->hashedPassword;
  }
}