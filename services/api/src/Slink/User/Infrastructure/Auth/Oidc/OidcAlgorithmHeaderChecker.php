<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth\Oidc;

use Slink\User\Domain\Contracts\AlgorithmHeaderCheckerInterface;
use Slink\User\Domain\Exception\UnsupportedJwtAlgorithmException;
use Slink\User\Domain\ValueObject\OAuth\JwtHeader;

final readonly class OidcAlgorithmHeaderChecker implements AlgorithmHeaderCheckerInterface {
  private const array SUPPORTED_ALGORITHMS = ['RS256', 'RS384', 'RS512', 'ES256', 'ES384', 'ES512'];

  #[\Override]
  public function check(JwtHeader $header): void {
    if (!in_array($header->getAlgorithm(), self::SUPPORTED_ALGORITHMS, true)) {
      throw new UnsupportedJwtAlgorithmException($header->getAlgorithm());
    }
  }
}
