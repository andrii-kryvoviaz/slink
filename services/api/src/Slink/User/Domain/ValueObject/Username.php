<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\Shared\Domain\ValueObject\EscapedString;
use Slink\Shared\Domain\ValueObject\SanitizableValueObject;
use Slink\User\Domain\Contracts\SuffixableName;
use Slink\User\Domain\Exception\InvalidUsernameException;

final readonly class Username extends AbstractValueObject implements SanitizableValueObject, SuffixableName {
  use FitsNumericSuffix;

  public const int MIN_LENGTH = 3;
  public const int MAX_LENGTH = 30;

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
   * @param int $suffix
   * @return static
   */
  public function withSuffix(int $suffix): static {
    return new self($this->fitSuffix($this->username, $suffix, self::MAX_LENGTH));
  }

  /**
   * @return string
   */
  public function toString(): string {
    return $this->username;
  }

  public function sanitize(): static {
    return clone($this, ['username' => EscapedString::fromString($this->username)->getValue()]);
  }
  
  /**
   * @param string $username
   * @return void
   */
  private function validate(string $username): void {
    if (strlen($username) < self::MIN_LENGTH) {
      throw new InvalidUsernameException(sprintf('Username must be at least %d characters long', self::MIN_LENGTH));
    }

    if (strlen($username) > self::MAX_LENGTH) {
      throw new InvalidUsernameException(sprintf('Username must be at most %d characters long', self::MAX_LENGTH));
    }
  }
}