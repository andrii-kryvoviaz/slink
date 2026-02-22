<?php

declare(strict_types=1);

namespace Slink\User\Domain\Contracts;

use Slink\User\Domain\ValueObject\OAuth\AuthorizationCode;
use Slink\User\Domain\ValueObject\OAuth\OAuthClaims;
use Slink\User\Domain\ValueObject\OAuth\OAuthState;
use Slink\User\Domain\ValueObject\OAuth\RedirectUri;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;

interface OAuthAdapterInterface {
  public function getAuthorizationUrl(OAuthProviderView $provider, RedirectUri $redirectUri): string;

  public function exchangeCode(AuthorizationCode $code, OAuthState $state): OAuthClaims;
}
