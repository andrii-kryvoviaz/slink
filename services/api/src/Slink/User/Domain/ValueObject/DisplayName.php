<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\User\Domain\Exception\InvalidDisplayNameException;

final readonly class DisplayName extends AbstractValueObject {
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
  
  /**
   * @param string $displayName
   * @return self
   */
  public static function fromString(string $displayName): self {
    return new self($displayName);
  }
  
  /**
   * @return string
   */
  public function toString(): string {
    return $this->displayName ?? $this->getReservedName();
  }
  
  /**
   * @param string $displayName
   * @return void
   */
  private function validate(string $displayName): void {
    if (strlen($displayName) < 3) {
      throw new InvalidDisplayNameException('Display name must be at least 3 characters long');
    }
    
    if (strlen($displayName) > 30) {
      throw new InvalidDisplayNameException('Display name must be at most 30 characters long');
    }
    
    $reservedNames = ['Anonymous', 'Guest'];

    foreach ($reservedNames as $reservedName) {
      if (strtolower($displayName) === strtolower($reservedName)) {
        throw new InvalidDisplayNameException(sprintf('`%s` is a reserved display name', $reservedName));
      }
    }
  }
}