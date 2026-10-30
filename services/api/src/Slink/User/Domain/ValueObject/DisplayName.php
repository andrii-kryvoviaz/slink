<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\Shared\Domain\ValueObject\EscapedString;
use Slink\Shared\Domain\ValueObject\SanitizableValueObject;
use Slink\User\Domain\Contracts\SuffixableName;
use Slink\User\Domain\Exception\InvalidDisplayNameException;

final readonly class DisplayName extends AbstractValueObject implements SanitizableValueObject, SuffixableName {
  use FitsNumericSuffix;

  public const int MIN_LENGTH = 3;
  public const int MAX_LENGTH = 30;

  /**
   * @return string
   */
  private function getReservedName(): string {
    return 'Guest';
  }
  
  /**
   * @param string|null $displayName
   */
  private function __construct(private ?string $displayName) {
    if ($displayName !== null) {
      $this->validate($displayName);
    }
  }
  
  /**
   * @param string|null $displayName
   * @return self
   */
  public static function fromNullableString(?string $displayName): self {
    if ($displayName === null) {
      return new self(null);
    }

    return self::fromString($displayName);
  }
  
  public static function fromStringOrNull(?string $displayName): ?self {
    if ($displayName === null || $displayName === '') {
      return null;
    }

    return self::fromString($displayName);
  }

  /**
   * @param string $displayName
   * @return self
   */
  public static function fromString(string $displayName): self {
    return new self($displayName);
  }
  
  /**
   * @param int $suffix
   * @return static
   */
  public function withSuffix(int $suffix): static {
    return new self($this->fitSuffix($this->toString(), $suffix, self::MAX_LENGTH, ' '));
  }

  /**
   * @return string
   */
  public function toString(): string {
    return $this->displayName ?? $this->getReservedName();
  }

  public function sanitize(): static {
    if ($this->displayName === null) {
      return $this;
    }

    return clone($this, ['displayName' => EscapedString::fromString($this->displayName)->getValue()]);
  }
  
  /**
   * @param string $displayName
   * @return void
   */
  private function validate(string $displayName): void {
    if (strlen($displayName) < self::MIN_LENGTH) {
      throw new InvalidDisplayNameException(sprintf('Display name must be at least %d characters long', self::MIN_LENGTH));
    }
    
    if (strlen($displayName) > self::MAX_LENGTH) {
      throw new InvalidDisplayNameException(sprintf('Display name must be at most %d characters long', self::MAX_LENGTH));
    }
    
    $reservedNames = ['Anonymous', 'Guest'];

    foreach ($reservedNames as $reservedName) {
      if (strtolower($displayName) === strtolower($reservedName)) {
        throw new InvalidDisplayNameException(sprintf('`%s` is a reserved display name', $reservedName));
      }
    }
  }
}