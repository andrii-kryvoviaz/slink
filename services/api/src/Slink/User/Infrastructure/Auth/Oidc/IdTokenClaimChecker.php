<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth\Oidc;

use Slink\User\Domain\Contracts\TimeClaimCheckerInterface;
use Slink\User\Domain\Exception\IdTokenClaimValidationException;
use Slink\User\Domain\ValueObject\OAuth\ClientId;
use Slink\User\Domain\ValueObject\OAuth\Issuer;
use Slink\User\Domain\ValueObject\OAuth\TokenClaims;

final readonly class IdTokenClaimChecker {
  public function __construct(
    private TimeClaimCheckerInterface $timeChecker,
  ) {}

  public function check(TokenClaims $claims, Issuer $issuer, ClientId $clientId): void {
    try {
      $this->timeChecker->check($claims);
    } catch (\Throwable $e) {
      throw IdTokenClaimValidationException::fromThrowable($e);
    }

    $claimIssuer = $claims->getIssuer();
    if ($claimIssuer === null || (string) $claimIssuer !== (string) $issuer) {
      throw new IdTokenClaimValidationException('Invalid issuer.');
    }

    $audience = (array) ($claims->toPayload()['aud'] ?? []);
    if (!in_array((string) $clientId, $audience, true)) {
      throw new IdTokenClaimValidationException('Invalid audience.');
    }
  }
}
