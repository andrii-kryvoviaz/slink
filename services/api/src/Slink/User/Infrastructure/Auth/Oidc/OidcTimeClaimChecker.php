<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth\Oidc;

use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Checker\ExpirationTimeChecker;
use Jose\Component\Checker\IssuedAtChecker;
use Jose\Component\Checker\NotBeforeChecker;
use Psr\Clock\ClockInterface;
use Slink\User\Domain\Contracts\TimeClaimCheckerInterface;
use Slink\User\Domain\ValueObject\OAuth\TokenClaims;

final readonly class OidcTimeClaimChecker implements TimeClaimCheckerInterface {
  private const int ALLOWED_TIME_DRIFT = 30;

  private ClaimCheckerManager $manager;

  public function __construct(ClockInterface $clock) {
    $this->manager = new ClaimCheckerManager([
      new ExpirationTimeChecker($clock, allowedTimeDrift: self::ALLOWED_TIME_DRIFT),
      new IssuedAtChecker($clock, allowedTimeDrift: self::ALLOWED_TIME_DRIFT),
      new NotBeforeChecker($clock, allowedTimeDrift: self::ALLOWED_TIME_DRIFT),
    ]);
  }

  #[\Override]
  public function check(TokenClaims $claims): void {
    $this->manager->check($claims->toPayload());
  }
}
