<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth\Oidc;

use Slink\User\Domain\Contracts\JwsVerifierInterface;
use Slink\User\Domain\ValueObject\OAuth\ClientId;
use Slink\User\Domain\ValueObject\OAuth\IdToken;
use Slink\User\Domain\ValueObject\OAuth\Issuer;
use Slink\User\Domain\ValueObject\OAuth\JwksUri;
use Slink\User\Domain\ValueObject\OAuth\TokenClaims;

final readonly class IdTokenVerifier {
  public function __construct(
    private JwsVerifierInterface $signatureVerifier,
    private IdTokenClaimChecker $claimChecker,
  ) {}

  public function verify(IdToken $idToken, JwksUri $jwksUri, Issuer $issuer, ClientId $clientId): TokenClaims {
    $claims = $this->signatureVerifier->verify($idToken, $jwksUri);

    $this->claimChecker->check($claims, $issuer, $clientId);

    return $claims;
  }
}
