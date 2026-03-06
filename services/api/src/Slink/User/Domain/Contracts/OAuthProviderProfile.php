<?php

declare(strict_types=1);

namespace Slink\User\Domain\Contracts;

use Slink\User\Domain\Enum\OAuthProvider;
use Slink\User\Domain\ValueObject\OAuth\ClientId;
use Slink\User\Domain\ValueObject\OAuth\ClientSecret;
use Slink\User\Domain\ValueObject\OAuth\DiscoveryUrl;
use Slink\User\Domain\ValueObject\OAuth\OAuthScopes;

interface OAuthProviderProfile {
  public function getSlug(): OAuthProvider;
  public function getClientId(): ClientId;
  public function getClientSecret(): ClientSecret;
  public function getDiscoveryUrl(): DiscoveryUrl;
  public function getScopes(): OAuthScopes;
  public function isEnabled(): bool;
}
