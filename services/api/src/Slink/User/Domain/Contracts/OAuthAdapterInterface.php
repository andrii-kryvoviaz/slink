<?php

declare(strict_types=1);

namespace Slink\User\Domain\Contracts;

use Slink\User\Domain\ValueObject\OAuth\AuthorizationCode;
use Slink\User\Domain\ValueObject\OAuth\OAuthIdentity;
use Slink\User\Domain\ValueObject\OAuth\OAuthState;
use Slink\User\Domain\ValueObject\OAuth\RedirectUri;
use Slink\User\Domain\Contracts\OAuthProviderProfile;

interface OAuthAdapterInterface {
  public function getAuthorizationUrl(OAuthProviderProfile $provider, RedirectUri $redirectUri): string;

  public function exchangeCode(AuthorizationCode $code, OAuthState $state): OAuthIdentity;
}
