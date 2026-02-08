<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\Auth;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;

final readonly class HashedApiKey extends AbstractValueObject {
  private function __construct(private string $hashedKey) {
  }

  public static function encode(#[\SensitiveParameter] string $plainKey): self {
    return new self(hash('sha256', $plainKey));
  }

  public static function fromHash(string $hash): self {
    return new self($hash);
  }

  public function verify(#[\SensitiveParameter] string $plainKey): bool {
    return hash_equals($this->hashedKey, hash('sha256', $plainKey));
  }

  #[\Override]
  public function toString(): string {
    return $this->hashedKey;
  }
}
