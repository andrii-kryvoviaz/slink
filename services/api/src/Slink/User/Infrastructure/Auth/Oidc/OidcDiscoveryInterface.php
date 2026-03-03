<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth\Oidc;

use Slink\User\Domain\ValueObject\OAuth\DiscoveryDocument;

interface OidcDiscoveryInterface {
  public function discover(string $discoveryUrl): DiscoveryDocument;
}
