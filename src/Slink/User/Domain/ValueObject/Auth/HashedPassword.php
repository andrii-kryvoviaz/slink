<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\Auth;

use SensitiveParameter;
use const PASSWORD_BCRYPT;
use function password_verify;
use RuntimeException;

final class HashedPassword implements \Stringable {
  public const COST = 12;

  private function __construct(private readonly string $hashedPassword) {
  }

  public static function encode(#[SensitiveParameter] string $plainPassword): self {
    return new self(self::hash($plainPassword));
  }

  public static function fromHash(string $hashedPassword): self {
    return new self($hashedPassword);
  }

  public function match(#[SensitiveParameter] string $plainPassword): bool {
    return password_verify($plainPassword, $this->hashedPassword);
  }

  private static function hash(#[SensitiveParameter] string $plainPassword): string {
    if (\mb_strlen($plainPassword) < 6) {
      throw new \InvalidArgumentException('Min 6 characters password');
    }

    /** @var string|bool|null $hashedPassword */
    $hashedPassword = \password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => self::COST]);

    if (\is_bool($hashedPassword)) {
      throw new RuntimeException('Server error hashing password');
    }

    return (string) $hashedPassword;
  }

  public function toString(): string {
    return $this->hashedPassword;
  }

  public function __toString(): string {
    return $this->hashedPassword;
  }
}