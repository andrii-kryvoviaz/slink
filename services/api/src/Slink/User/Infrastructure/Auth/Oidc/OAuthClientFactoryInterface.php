<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth\Oidc;

use League\OAuth2\Client\Provider\GenericProvider;
use Slink\User\Domain\Contracts\OAuthProviderProfile;
use Slink\User\Domain\ValueObject\OAuth\RedirectUri;

interface OAuthClientFactoryInterface {
  public function create(OAuthProviderProfile $provider, RedirectUri $redirectUri): GenericProvider;
}
