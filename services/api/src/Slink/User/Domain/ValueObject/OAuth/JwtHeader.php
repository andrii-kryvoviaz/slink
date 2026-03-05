<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\OAuth;

use Slink\Shared\Domain\Exception\InvalidValueObjectException;
use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\User\Domain\Exception\MissingKeyIdException;
use Slink\User\Domain\Exception\UnsupportedJwtAlgorithmException;

final readonly class JwtHeader extends AbstractValueObject {
  private function __construct(
    private string $algorithm,
    private string $keyId,
  ) {
    if (JwsAlgorithm::tryFrom($algorithm) === null) {
      throw new UnsupportedJwtAlgorithmException($algorithm);
    }
  }

  /**
   * @param array<string, mixed> $header
   */
  public static function fromPayload(array $header): self {
    $alg = $header['alg'] ?? null;

    if (!is_string($alg)) {
      throw new InvalidValueObjectException('JwtHeader', 'algorithm must be a non-empty string');
    }

    $kid = $header['kid'] ?? null;

    if (!is_string($kid)) {
      throw new MissingKeyIdException();
    }

    return new self($alg, $kid);
  }

  public function getAlgorithm(): string {
    return $this->algorithm;
  }

  public function getKeyId(): string {
    return $this->keyId;
  }

  public function toString(): string {
    return $this->algorithm;
  }
}
