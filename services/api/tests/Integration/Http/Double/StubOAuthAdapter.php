<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Double;

use Slink\User\Domain\Contracts\OAuthAdapterInterface;
use Slink\User\Domain\Contracts\OAuthProviderProfile;
use Slink\User\Domain\Exception\InvalidCredentialsException;
use Slink\User\Domain\ValueObject\OAuth\AuthorizationCode;
use Slink\User\Domain\ValueObject\OAuth\OAuthIdentity;
use Slink\User\Domain\ValueObject\OAuth\OAuthState;
use Slink\User\Domain\ValueObject\OAuth\RedirectUri;

final class StubOAuthAdapter implements OAuthAdapterInterface {
  private static ?OAuthIdentity $identity = null;

  public static function setIdentity(OAuthIdentity $identity): void {
    self::$identity = $identity;
  }

  public static function reset(): void {
    self::$identity = null;
  }

  #[\Override]
  public function getAuthorizationUrl(OAuthProviderProfile $provider, RedirectUri $redirectUri): string {
    return 'https://sso.test/authorize';
  }

  #[\Override]
  public function exchangeCode(AuthorizationCode $code, OAuthState $state): OAuthIdentity {
    return self::$identity ?? throw new InvalidCredentialsException();
  }
}
