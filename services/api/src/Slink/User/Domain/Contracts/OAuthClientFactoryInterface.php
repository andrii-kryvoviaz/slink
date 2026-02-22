<?php

declare(strict_types=1);

namespace Slink\User\Domain\Contracts;

use League\OAuth2\Client\Provider\GenericProvider;
use Slink\User\Domain\ValueObject\OAuth\RedirectUri;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;

interface OAuthClientFactoryInterface {
  public function create(OAuthProviderView $provider, RedirectUri $redirectUri): GenericProvider;
}
