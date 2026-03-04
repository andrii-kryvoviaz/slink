<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth\Oidc;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\Algorithm\ES384;
use Jose\Component\Signature\Algorithm\ES512;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\Algorithm\RS384;
use Jose\Component\Signature\Algorithm\RS512;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Slink\User\Domain\Contracts\AlgorithmHeaderCheckerInterface;
use Slink\User\Domain\Contracts\JwsVerifierInterface;
use Slink\User\Domain\Exception\IdTokenParsingException;
use Slink\User\Domain\Exception\InvalidJwsSignatureException;
use Slink\User\Domain\Exception\MissingKeyIdException;
use Slink\User\Domain\ValueObject\OAuth\IdToken;
use Slink\User\Domain\ValueObject\OAuth\JwksUri;
use Slink\User\Domain\ValueObject\OAuth\JwtHeader;
use Slink\User\Domain\ValueObject\OAuth\TokenClaims;

final readonly class JwsSignatureVerifier implements JwsVerifierInterface {
  private CompactSerializer $serializer;
  private JWSVerifier $verifier;

  public function __construct(
    private JwksProvider $jwksProvider,
    private AlgorithmHeaderCheckerInterface $headerChecker,
  ) {
    $this->serializer = new CompactSerializer();

    $this->verifier = new JWSVerifier(new AlgorithmManager([
      new RS256(),
      new RS384(),
      new RS512(),
      new ES256(),
      new ES384(),
      new ES512(),
    ]));
  }

  #[\Override]
  public function verify(IdToken $idToken, JwksUri $jwksUri): TokenClaims {
    try {
      $jws = $this->serializer->unserialize((string) $idToken);
    } catch (\InvalidArgumentException $e) {
      throw new IdTokenParsingException($e);
    }

    $header = JwtHeader::fromProtectedHeader($jws->getSignature(0)->getProtectedHeader());

    $this->headerChecker->check($header);

    $kid = $header->getKeyId();
    if ($kid === null) {
      throw new MissingKeyIdException();
    }

    $jwkSet = $this->jwksProvider->getKeySet((string) $jwksUri, $kid);

    if (!$this->verifier->verifyWithKeySet($jws, $jwkSet, 0)) {
      throw new InvalidJwsSignatureException(
        new \RuntimeException('JWS signature verification failed'),
      );
    }

    return TokenClaims::fromPayload(
      json_decode($jws->getPayload() ?? '', true, 512, JSON_THROW_ON_ERROR),
    );
  }
}
