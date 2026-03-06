<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth\Oidc;

use Slink\User\Domain\ValueObject\OAuth\DiscoveryDocument;
use Slink\User\Domain\ValueObject\OAuth\DiscoveryUrl;

interface OidcDiscoveryInterface {
  public function discover(DiscoveryUrl $discoveryUrl): DiscoveryDocument;
}
