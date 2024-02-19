<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;

final readonly class DisplayName extends AbstractValueObject {
  /**
   * @return string
   */
  private static function getSystemName(): string {
    return 'Anonymous';
  }
  
  /**
   * @param string|null $displayName
   */
  private function __construct(private ?string $displayName) {
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
    self::validate($displayName);

    return new self($displayName);
  }
  
  /**
   * @return string
   */
  public function toString(): string {
    return $this->displayName ?? self::getSystemName();
  }
  
  /**
   * @param string $displayName
   * @return void
   */
  private static function validate(string $displayName): void {
    if (strlen($displayName) < 3) {
      throw new \InvalidArgumentException('Display name must be at least 3 characters long');
    }
    
    if (strlen($displayName) > 30) {
      throw new \InvalidArgumentException('Display name must be at most 30 characters long');
    }
    
    if(strtolower($displayName) === strtolower(self::getSystemName())) {
      throw new \InvalidArgumentException(sprintf('`%s` is a reserved display name', self::getSystemName()));
    }
  }
}