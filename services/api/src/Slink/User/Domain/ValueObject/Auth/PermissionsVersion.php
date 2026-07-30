<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\Auth;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\User\Domain\Enum\RevocationScope;

final readonly class PermissionsVersion extends AbstractValueObject {
  private function __construct(
    private RevocationScope $scope,
    private int $revokedBefore,
  ) {
  }

  public static function never(): self {
    return new self(RevocationScope::None, 0);
  }

  public static function bumpedAt(int $timestamp): self {
    return new self(RevocationScope::IssuedBefore, $timestamp);
  }

  public static function terminal(): self {
    return new self(RevocationScope::All, 0);
  }

  public function invalidates(int $issuedAt): bool {
    return match ($this->scope) {
      RevocationScope::None => false,
      RevocationScope::IssuedBefore => $issuedAt < $this->revokedBefore,
      RevocationScope::All => true,
    };
  }
}
