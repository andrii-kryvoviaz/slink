<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\OAuth;

use Slink\Shared\Domain\Exception\InvalidValueObjectException;
use Slink\Shared\Domain\ValueObject\AbstractValueObject;

final readonly class JwtHeader extends AbstractValueObject {
  private function __construct(
    private string $algorithm,
    private ?string $keyId,
  ) {
    if ($algorithm === '') {
      throw new InvalidValueObjectException('JwtHeader', 'algorithm cannot be empty');
    }
  }

  /**
   * @param array<string, mixed> $header
   */
  public static function fromProtectedHeader(array $header): self {
    $alg = $header['alg'] ?? null;

    if (!is_string($alg) || $alg === '') {
      throw new InvalidValueObjectException('JwtHeader', 'algorithm must be a non-empty string');
    }

    return new self($alg, $header['kid'] ?? null);
  }

  public function getAlgorithm(): string {
    return $this->algorithm;
  }

  public function getKeyId(): ?string {
    return $this->keyId;
  }

  public function toString(): string {
    return $this->algorithm;
  }
}
