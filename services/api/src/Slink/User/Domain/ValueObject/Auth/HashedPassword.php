<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\Auth;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\User\Domain\Service\BcryptHasher;

final readonly class HashedPassword extends AbstractValueObject {
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
    return new self(BcryptHasher::hash($plainPassword));
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
   * @return string
   */
  #[\Override]
  public function toString(): string {
    return $this->hashedPassword;
  }
}