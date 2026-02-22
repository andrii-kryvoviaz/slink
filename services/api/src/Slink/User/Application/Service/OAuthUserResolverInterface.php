<?php

declare(strict_types=1);

namespace Slink\User\Application\Service;

use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\OAuth\OAuthClaims;

interface OAuthUserResolverInterface {
  public function resolve(OAuthClaims $claims): User;
}
