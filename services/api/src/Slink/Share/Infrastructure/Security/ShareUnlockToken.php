<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\Security;

final readonly class ShareUnlockToken {
  private const string SEPARATOR = '.';

  public function __construct(
    public int $expiresTimestamp,
    public string $signature,
  ) {}

  public static function fromString(string $raw): ?self {
    $parts = \explode(self::SEPARATOR, $raw, 2);

    if (\count($parts) !== 2) {
      return null;
    }

    [$expiresRaw, $signature] = $parts;

    if (!\ctype_digit($expiresRaw)) {
      return null;
    }

    return new self((int) $expiresRaw, $signature);
  }

  public function toString(): string {
    return \implode(self::SEPARATOR, [$this->expiresTimestamp, $this->signature]);
  }

  public function isExpired(): bool {
    return $this->expiresTimestamp < \time();
  }
}
