<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\Security;

final readonly class SignedShareUnlock {
  public function __construct(
    public string $value,
    public \DateTimeImmutable $expiresAt,
  ) {}
}
