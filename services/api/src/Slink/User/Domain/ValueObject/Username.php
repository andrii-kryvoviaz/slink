<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\Shared\Domain\ValueObject\EscapedString;
use Slink\Shared\Domain\ValueObject\SanitizableValueObject;
use Slink\User\Domain\Exception\InvalidUsernameException;

final readonly class Username extends AbstractValueObject implements SanitizableValueObject {
  /**
   * @param string $username
   */
  private function __construct(private string $username) {
    $this->validate($username);
  }
  
  /**
   * @param string $username
   * @return self
   */
  public static function fromString(string $username): self {
    return new self($username);
  }
  
  /**
   * @param DisplayName $displayName
   * @return self
   */
  public static function fromDisplayName(DisplayName $displayName): self {
    $username = str_replace(' ', '.', trim($displayName->toString()));
    
    return new self(strtolower($username));
  }
  
  /**
   * @return string
   */
  public function toString(): string {
    return $this->username;
  }

  public function sanitize(): static {
    return new self(EscapedString::fromString($this->username)->getValue());
  }
  
  /**
   * @param string $username
   * @return void
   */
  private function validate(string $username): void {
    if (strlen($username) < 3) {
      throw new InvalidUsernameException('Username must be at least 3 characters long');
    }
    
    if (strlen($username) > 30) {
      throw new InvalidUsernameException('Username must be at most 30 characters long');
    }
  }
}