<?php

declare(strict_types=1);

namespace Slink\Share\Domain\ValueObject;

use RuntimeException;
use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\User\Domain\Exception\InvalidPasswordException;

final readonly class HashedSharePassword extends AbstractValueObject {
  private const int COST = 12;
  private const int MIN_LENGTH = 6;

  private function __construct(private string $hashedPassword) {
  }

  public static function encode(#[\SensitiveParameter] string $plainPassword): self {
    return new self(self::hash($plainPassword));
  }

  public static function fromNullable(#[\SensitiveParameter] ?string $plainPassword): ?self {
    return $plainPassword === null ? null : self::encode($plainPassword);
  }

  public static function fromHash(string $hashedPassword): self {
    return new self($hashedPassword);
  }

  public static function fromNullableHash(?string $hashedPassword): ?self {
    return $hashedPassword === null ? null : self::fromHash($hashedPassword);
  }

  public function match(#[\SensitiveParameter] string $plainPassword): bool {
    return \password_verify($plainPassword, $this->hashedPassword);
  }

  public function equals(?AbstractValueObject $other): bool {
    if (!$other instanceof self) {
      return false;
    }

    return \hash_equals($this->hashedPassword, $other->hashedPassword);
  }

  private static function hash(#[\SensitiveParameter] string $plainPassword): string {
    if (\mb_strlen($plainPassword) < self::MIN_LENGTH) {
      throw new InvalidPasswordException('Min 6 characters password');
    }

    $hashedPassword = \password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => self::COST]);

    if (!$hashedPassword) {
      throw new RuntimeException('Server error hashing password');
    }

    return $hashedPassword;
  }

  #[\Override]
  public function toString(): string {
    return $this->hashedPassword;
  }
}
