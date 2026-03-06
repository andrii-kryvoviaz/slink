<?php

declare(strict_types=1);

namespace Slink\User\Domain\Contracts;

use Slink\User\Domain\ValueObject\OAuth\ClientId;

interface OAuthProviderProfile {
  public function getSlug(): string;
  public function getClientId(): ClientId;
  public function getClientSecret(): string;
  public function getDiscoveryUrl(): string;
  public function getScopes(): string;
  public function isEnabled(): bool;
}
