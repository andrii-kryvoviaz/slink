<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth\Oidc;

use Jose\Component\Signature\JWS;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Slink\User\Domain\Exception\IdTokenParsingException;
use Slink\User\Domain\ValueObject\OAuth\IdToken;
use Slink\User\Domain\ValueObject\OAuth\TokenClaims;

final readonly class JwsParser {
  public function __construct(
    private CompactSerializer $serializer,
  ) {}

  public function parse(IdToken $idToken): JWS {
    try {
      return $this->serializer->unserialize((string) $idToken);
    } catch (\InvalidArgumentException $e) {
      throw new IdTokenParsingException($e);
    }
  }

  public function extractClaims(JWS $jws): TokenClaims {
    return TokenClaims::fromPayload(
      json_decode($jws->getPayload() ?? '', true),
    );
  }
}
