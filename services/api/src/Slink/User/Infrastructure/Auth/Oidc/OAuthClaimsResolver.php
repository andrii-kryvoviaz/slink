<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth\Oidc;

use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Signature\JWSVerifier;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use Slink\User\Domain\Enum\OAuthProvider;
use Slink\User\Domain\Exception\IdTokenClaimValidationException;
use Psr\Log\LoggerInterface;
use Slink\User\Domain\Exception\InvalidJwsSignatureException;
use Slink\User\Domain\Exception\MissingIdTokenException;
use Slink\User\Domain\ValueObject\OAuth\IdToken;
use Slink\User\Domain\ValueObject\OAuth\JwtHeader;
use Slink\User\Domain\ValueObject\OAuth\OAuthIdentity;
use Slink\User\Domain\ValueObject\OAuth\TokenClaims;
use Slink\User\Domain\Contracts\OAuthProviderProfile;

final readonly class OAuthClaimsResolver {
  public function __construct(
    private JwsParser $jwsParser,
    private JwksProvider $jwksProvider,
    private JWSVerifier $jwsVerifier,
    private ClaimCheckerManager $claimCheckerManager,
    private OidcDiscoveryInterface $oidcDiscovery,
    private LoggerInterface $logger,
  ) {}

  public function resolve(GenericProvider $client, AccessToken $accessToken, OAuthProviderProfile $provider): OAuthIdentity {
    $idToken = IdToken::fromPayload($accessToken->getValues());

    if ($idToken === null) {
      $this->logger->warning('OIDC provider did not return an id_token, denying authentication', [
        'provider' => $provider->getSlug(),
      ]);

      throw new MissingIdTokenException($provider->getSlug());
    }

    $discovery = $this->oidcDiscovery->discover($provider->getDiscoveryUrl());

    $jws = $this->jwsParser->parse($idToken);

    $header = JwtHeader::fromPayload($jws->getSignature(0)->getProtectedHeader());

    $jwkSet = $this->jwksProvider->getKeySet((string) $discovery->getJwksUri(), $header->getKeyId());
    if (!$this->jwsVerifier->verifyWithKeySet($jws, $jwkSet, 0)) {
      throw new InvalidJwsSignatureException(
        new \RuntimeException('JWS signature verification failed'),
      );
    }

    $claims = $this->jwsParser->extractClaims($jws);

    try {
      $this->claimCheckerManager->check($claims->toPayload());
    } catch (\Throwable $e) {
      throw IdTokenClaimValidationException::fromThrowable($e);
    }

    if (!$claims->isIssuedBy($discovery->getIssuer())) {
      throw new IdTokenClaimValidationException('Invalid issuer.');
    }

    if (!$claims->hasAudience($provider->getClientId())) {
      throw new IdTokenClaimValidationException('Invalid audience.');
    }

    return OAuthIdentity::fromTokenClaims($claims, OAuthProvider::from($provider->getSlug()));
  }

}
