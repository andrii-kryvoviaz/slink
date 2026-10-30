<?php

declare(strict_types=1);

namespace Slink\Share\Domain\ValueObject;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\User\Domain\Service\BcryptHasher;

final readonly class HashedSharePassword extends AbstractValueObject {
  private function __construct(private string $hashedPassword) {
  }

  public static function encode(#[\SensitiveParameter] string $plainPassword): self {
    return new self(BcryptHasher::hash($plainPassword));
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

  #[\Override]
  public function toString(): string {
    return $this->hashedPassword;
  }
}
